<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    public function generateTicketsForOrder(Order $order): void
    {
        // Siparişteki adet kadar bilet oluştur
        for ($i = 0; $i < $order->tickets()->count(); $i++) {
            // NOT: CreateOrder aşamasında bilet adedini quantity olarak tuttuk.
            // Ama biletleri fiziksel olarak yaratmadık. Şimdi yaratıyoruz.
        }

        // DÜZELTME: Order modelinde quantity alanı yok, quantity ara tabloda değil Order'ın kendisinde var.
        // OrderService'de quantity kadar dönmemiz lazım.

        // Order modeline quantity eklemeyi unutmuş olabiliriz migration'da?
        // Kontrol ettim: create_orders_table migration'ında 'quantity' kolonu YOK.
        // HATA TESPİTİ: Order tablosunda 'quantity' veya 'event_ticket_type_id' tutmamışız.
        // Migration adımında sadece total_amount tutmuşuz. Bu bir eksiklik.
        // ÇÖZÜM: MVP için OrderItem tablosu yapmadık, Order tek tip bilet alıyor varsaydık.
        // Ama Order tablosuna hangi biletten kaç tane aldığını yazmamışız.
    }
}
