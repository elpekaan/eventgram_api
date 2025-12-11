<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Services;

use App\Contracts\Services\CheckInServiceInterface;
use App\Modules\CheckIn\DTOs\CheckInRequestDTO;
use App\Modules\CheckIn\DTOs\CheckInResponseDTO;
use App\Modules\CheckIn\Events\TicketCheckedIn;
use App\Modules\CheckIn\Models\CheckIn;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckInService implements CheckInServiceInterface
{
    private const GEOFENCE_RADIUS_METERS = 100;

    /**
     * Process ticket check-in with full validation
     */
    public function checkIn(CheckInRequestDTO $dto): CheckInResponseDTO
    {
        return DB::transaction(function () use ($dto) {
            // 1. Find ticket
            $ticket = Ticket::where('ticket_code', $dto->ticketCode)
                ->with(['event.venue', 'user', 'ticketType'])
                ->lockForUpdate() // Pessimistic lock
                ->first();

            if (!$ticket) {
                throw ValidationException::withMessages([
                    'ticket_code' => ['Geçersiz bilet kodu.']
                ]);
            }

            // 2. Load event
            $event = $ticket->event;

            // 3. Validate staff authorization
            $this->validateStaffAuthorization($dto->staffId, $event->venue->user_id);

            // 4. Validate ticket status
            $this->validateTicketStatus($ticket);

            // 5. Validate check-in window
            $this->validateCheckInWindow($event);

            // 6. Check for duplicate
            $this->validateNoDuplicate($ticket->id);

            // 7. Validate geo-location (if provided)
            $locationVerified = false;
            if ($dto->latitude && $dto->longitude) {
                $locationVerified = $this->validateGeofence(
                    $dto->latitude,
                    $dto->longitude,
                    $event->venue->latitude,
                    $event->venue->longitude
                );
            }

            // 8. Determine if check-in is late
            $wasLate = now() > $event->date;

            // 9. Create check-in record
            $checkIn = CheckIn::create([
                'ticket_id' => $ticket->id,
                'event_id' => $event->id,
                'user_id' => $ticket->user_id,
                'checked_in_by' => $dto->staffId,
                'checked_in_at' => now(),
                'device_id' => $dto->deviceId,
                'device_info' => $dto->deviceInfo,
                'latitude' => $dto->latitude,
                'longitude' => $dto->longitude,
                'location_verified' => $locationVerified,
                'is_valid' => true,
                'validation_status' => 'valid',
            ]);

            // 10. Update ticket status
            $ticket->update([
                'status' => 'used',
                'used_at' => now(),
                'checked_in_by' => $dto->staffId,
            ]);

            // 11. Increment event checked_in_count
            $event->increment('checked_in_count');

            // 12. Fire event
            event(new TicketCheckedIn($checkIn));

            // 13. Log
            Log::info('Ticket checked in', [
                'ticket_id' => $ticket->id,
                'event_id' => $event->id,
                'staff_id' => $dto->staffId,
                'was_late' => $wasLate,
                'location_verified' => $locationVerified,
            ]);

            // 14. Build response
            $message = $this->buildSuccessMessage($ticket, $wasLate, $locationVerified);

            return new CheckInResponseDTO(
                checkIn: $checkIn,
                ticket: $ticket,
                wasLate: $wasLate,
                locationVerified: $locationVerified,
                message: $message,
            );
        });
    }

    /**
     * Get all check-ins for an event
     */
    public function getEventCheckIns(int $eventId): array
    {
        return CheckIn::forEvent($eventId)
            ->with(['ticket.user', 'staff'])
            ->valid()
            ->orderBy('checked_in_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get check-in statistics for an event
     */
    public function getCheckInStats(int $eventId): array
    {
        $total = CheckIn::forEvent($eventId)->valid()->count();
        $onTime = CheckIn::forEvent($eventId)
            ->valid()
            ->whereHas('event', function ($q) {
                $q->whereRaw('check_ins.checked_in_at <= events.date');
            })
            ->count();
        $late = $total - $onTime;
        $locationVerified = CheckIn::forEvent($eventId)
            ->valid()
            ->locationVerified()
            ->count();

        return [
            'total_check_ins' => $total,
            'on_time' => $onTime,
            'late' => $late,
            'location_verified' => $locationVerified,
        ];
    }

    /**
     * Validate staff has authorization for this venue
     */
    private function validateStaffAuthorization(int $staffId, int $venueOwnerId): void
    {
        if ($staffId !== $venueOwnerId) {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bileti kontrol etme yetkiniz yok.']
            ]);
        }
    }

    /**
     * Validate ticket can be checked in
     */
    private function validateTicketStatus(Ticket $ticket): void
    {
        if ($ticket->status === 'used') {
            throw ValidationException::withMessages([
                'ticket_code' => [
                    "Bu bilet daha önce kullanılmış! (Giriş: {$ticket->used_at?->format('d.m.Y H:i')})"
                ]
            ]);
        }

        if ($ticket->status === 'cancelled') {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet iptal edilmiş!']
            ]);
        }

        if ($ticket->status === 'refunded') {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet iade edilmiş!']
            ]);
        }

        if ($ticket->status === 'transferred') {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet transfer edilmiş!']
            ]);
        }
    }

    /**
     * Validate check-in window
     */
    private function validateCheckInWindow($event): void
    {
        $now = now();
        $checkInOpens = $event->date->copy()->subHours($event->check_in_opens_hours);
        $checkInCloses = $event->date->copy()->addHours($event->late_entry_hours);

        if ($now < $checkInOpens) {
            throw ValidationException::withMessages([
                'ticket_code' => [
                    "Check-in henüz açılmadı. Açılış: {$checkInOpens->format('d.m.Y H:i')}"
                ]
            ]);
        }

        if (!$event->allow_late_entry && $now > $checkInCloses) {
            throw ValidationException::withMessages([
                'ticket_code' => ['Check-in süresi dolmuş! Geç giriş kabul edilmiyor.']
            ]);
        }
    }

    /**
     * Validate no duplicate check-in
     */
    private function validateNoDuplicate(int $ticketId): void
    {
        $exists = CheckIn::where('ticket_id', $ticketId)
            ->where('is_valid', true)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'ticket_code' => ['Bu bilet için zaten geçerli bir check-in kaydı var!']
            ]);
        }
    }

    /**
     * Validate location is within geofence
     */
    private function validateGeofence(
        float $userLat,
        float $userLon,
        ?float $venueLat,
        ?float $venueLon
    ): bool {
        if (!$venueLat || !$venueLon) {
            return false;
        }

        $distance = $this->calculateDistance($userLat, $userLon, $venueLat, $venueLon);

        return $distance <= self::GEOFENCE_RADIUS_METERS;
    }

    /**
     * Calculate distance using Haversine formula
     */
    private function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Build success message
     */
    private function buildSuccessMessage(
        Ticket $ticket,
        bool $wasLate,
        bool $locationVerified
    ): string {
        $message = "✅ Giriş başarılı! {$ticket->user->name} - {$ticket->ticketType->name}";

        if ($wasLate) {
            $message .= " (GEÇ GİRİŞ)";
        }

        if (!$locationVerified) {
            $message .= " ⚠️ Konum doğrulanamadı";
        }

        return $message;
    }
}
