<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    // Constructor Injection ile TicketService'i alalım
    public function __construct(
        protected TicketService $ticketService
    ) {}

    // ... createOrder metodu ...

    /**
     * Ödeme başarılı olduğunda çağrılır.
     */
    public function completeOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // 1. Sipariş durumunu güncelle
            $order->update(['status' => OrderStatus::PAID]);

            // 2. Biletleri oluştur
            $this->ticketService->generateTickets($order);

            // 3. (İleride) Email gönderim Job'ı buraya gelecek
        });
    }
}
