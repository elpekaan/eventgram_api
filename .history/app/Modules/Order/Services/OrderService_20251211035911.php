<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Ticket\Services\TicketService;
use App\Modules\Event\Models\EventTicketType;
use App\Modules\Order\DTOs\CreateOrderDTO;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Order\Jobs\SendTicketEmailJob;



class OrderService
{
    public function __construct(
        protected TicketService $ticketService // Bu var mı kontrol et
    ) {}

    /**
     * Sipariş oluşturma ve stok düşme (Atomic Operation)
     */
    public function createOrder(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // 1. Bilet Tipini bul ve KİLİTLE (Pessimistic Lock)
            // 'lockForUpdate' diğer okumaları bloklar.
            /** @var EventTicketType $ticketType */
            $ticketType = EventTicketType::where('id', $dto->ticketTypeId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Stok Kontrolü
            // Bilet kapasitesi, satılan miktardan ve istenen miktardan büyük mü?
            $available = $ticketType->capacity - $ticketType->sold_count;

            if ($available < $dto->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ["Üzgünüz, sadece {$available} adet bilet kaldı."],
                ]);
            }

            // 3. Stoktan Düş (Reserve Et)
            // Bu aşamada sold_count'u artırıyoruz.
            // Eğer ödeme başarısız olursa, bir Cron Job veya Webhook ile bunu geri alacağız.
            $ticketType->increment('sold_count', $dto->quantity);

            // 4. Toplam Tutarı Hesapla
            $totalAmount = $ticketType->price * $dto->quantity;

            // 5. Siparişi Oluştur
            $order = Order::create([
                'user_id' => $dto->userId,
                'event_id' => $dto->eventId,
                'event_ticket_type_id' => $dto->ticketTypeId,
                'quantity' => $dto->quantity,
                'reference_code' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'status' => OrderStatus::PENDING,
                'expires_at' => now()->addMinutes(10),
            ]);

            return $order;
        });
    }

    /**
     * Ödeme başarılı olduğunda çağrılır.
     */
    public function completeOrder(Order $order, PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($order, $transaction) {
            // 1. Sipariş durumunu güncelle ve Transaction'ı bağla
            $order->update([
                'status' => OrderStatus::PAID,
                'payment_transaction_id' => $transaction->id, // <--- BAĞLANTI
            ]);

            // 2. Biletleri oluştur
            $this->ticketService->generateTickets($order);
        });
    }
}
