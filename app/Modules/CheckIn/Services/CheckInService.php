<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Services;

use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\User\Models\User;
use Illuminate\Validation\ValidationException;

class CheckInService
{
    public function verifyAndProcess(string $ticketCode, User $staff): Ticket
    {
        // 1. Bileti Bul (Koda göre)
        $ticket = Ticket::where('code', $ticketCode)
            ->with(['event.venue', 'user'])
            ->first();

        if (! $ticket) {
            throw ValidationException::withMessages(['code' => 'Geçersiz bilet kodu.']);
        }

        // 2. Yetki Kontrolü (Staff bu mekana mı bakıyor?)
        // Basit MVP kuralı: Sadece Mekan Sahibi (Venue Owner) check-in yapabilir.
        // İleride "Kapı Görevlisi" rolü eklenince burası güncellenir.
        $venueOwnerId = $ticket->event->venue->user_id;

        if ($staff->id !== $venueOwnerId) {
            throw ValidationException::withMessages(['code' => 'Bu bileti kontrol etme yetkiniz yok (Başka mekan).']);
        }

        // 3. Statü Kontrolü
        if ($ticket->status === TicketStatus::USED) {
            throw ValidationException::withMessages([
                'code' => "Bu bilet daha önce kullanılmış! (Giriş saati: {$ticket->used_at?->format('H:i')})"
            ]);
        }

        if ($ticket->status === TicketStatus::CANCELLED) {
            throw ValidationException::withMessages(['code' => 'Bu bilet iptal edilmiş!']);
        }

        // 4. Bileti "Kullanıldı" Olarak İşaretle
        $ticket->update([
            'status' => TicketStatus::USED,
            'used_at' => now(),
        ]);

        return $ticket;
    }
}
