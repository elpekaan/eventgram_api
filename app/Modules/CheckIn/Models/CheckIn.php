<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Models;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\Event\Models\Event;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $event_id
 * @property int $user_id
 * @property int $checked_in_by
 * @property \Illuminate\Support\Carbon $checked_in_at
 * @property string|null $device_id
 * @property string|null $device_info
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool $location_verified
 * @property bool $is_valid
 * @property string $validation_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Ticket $ticket
 * @property-read Event $event
 * @property-read User $user
 * @property-read User $staff
 * @property-read bool $was_late
 */
class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'event_id',
        'user_id',
        'checked_in_by',
        'checked_in_at',
        'device_id',
        'device_info',
        'latitude',
        'longitude',
        'location_verified',
        'is_valid',
        'validation_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_verified' => 'boolean',
            'is_valid' => 'boolean',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The ticket that was checked in
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * The event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The ticket owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Staff member who performed check-in
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Only valid check-ins
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_valid', true)
                    ->where('validation_status', 'valid');
    }

    /**
     * Scope: Check-ins for a specific event
     */
    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope: Check-ins by a specific staff member
     */
    public function scopeByStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('checked_in_by', $staffId);
    }

    /**
     * Scope: Check-ins today
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('checked_in_at', today());
    }

    /**
     * Scope: Check-ins within date range
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('checked_in_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Location verified check-ins
     */
    public function scopeLocationVerified(Builder $query): Builder
    {
        return $query->where('location_verified', true);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Check if check-in was late (after event start)
     */
    public function getWasLateAttribute(): bool
    {
        return $this->checked_in_at > $this->event->date;
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Verify location is within geofence
     */
    public function verifyLocation(float $venueLatitude, float $venueLongitude, float $radiusInMeters = 100): bool
    {
        if (!$this->latitude || !$this->longitude) {
            return false;
        }

        $distance = $this->calculateDistance(
            $this->latitude,
            $this->longitude,
            $venueLatitude,
            $venueLongitude
        );

        $isWithinRadius = $distance <= $radiusInMeters;

        if ($isWithinRadius) {
            $this->update(['location_verified' => true]);
        }

        return $isWithinRadius;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
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
     * Mark as invalid
     */
    public function markAsInvalid(string $reason, ?string $notes = null): void
    {
        $this->update([
            'is_valid' => false,
            'validation_status' => $reason,
            'notes' => $notes,
        ]);
    }
}
