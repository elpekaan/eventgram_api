<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Sipariş tamamlandığında biletleri oluşturur.
     */
    public function generateTickets(Order $order): void
    {
        // Quantity kadar döngü kurup bilet oluşturuyoruz
        for ($i = 0; $i < $order->quantity; $i++) {
            Ticket::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'event_id' => $order->event_id,
                'event_ticket_type_id' => $order->event_ticket_type_id,
                'code' => $this->generateUniqueCode(),
                'status' => TicketStatus::ACTIVE,
            ]);
        }
    }

    private function generateUniqueCode(): string
    {
        // Benzersiz bir bilet kodu üret (Örn: TIC-8X92M)
        do {
            $code = 'TIC-' . strtoupper(Str::random(8));
        } while (Ticket::where('code', $code)->exists());

        return $code;
    }
}
