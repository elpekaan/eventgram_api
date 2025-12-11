<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Contracts\Services\OrderServiceInterface;
use App\Modules\Order\DTOs\CreateOrderDTO;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Models\Order;
use App\Modules\Event\Models\EventTicketType;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Ticket\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderService implements OrderServiceInterface
{
    private const ORDER_EXPIRY_MINUTES = 15;
    private const SERVICE_FEE_RATE = 0.10; // 10%
    private const POINTS_PER_TRY = 1;

    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * Create order and reserve tickets
     */
    public function createOrder(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // 1. Find and lock ticket type
            $ticketType = EventTicketType::where('id', $dto->ticketTypeId)
                ->with('event')
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Validate quantity
            $this->validateQuantity($ticketType, $dto->quantity);

            // 3. Reserve tickets (decrement available, increment reserved)
            if (!$ticketType->reserve($dto->quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => ['Yeterli bilet kalmamış!']
                ]);
            }

            // 4. Calculate pricing
            $subtotal = $ticketType->price * $dto->quantity;
            $serviceFee = $subtotal * self::SERVICE_FEE_RATE;
            $discount = 0; // TODO: Apply coupon if provided
            $total = $subtotal + $serviceFee - $discount;

            // 5. Calculate points
            $pointsEarned = (int) floor($total * self::POINTS_PER_TRY);

            // 6. Generate order number
            $orderNumber = $this->generateOrderNumber();

            // 7. Create order
            $order = Order::create([
                'user_id' => $dto->userId,
                'event_id' => $dto->eventId,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'total' => $total,
                'coupon_code' => $dto->couponCode,
                'discount' => $discount,
                'payment_transaction_id' => null,
                'status' => 'pending_payment',
                'ip_address' => $dto->ipAddress,
                'user_agent' => $dto->userAgent,
                'points_earned' => $pointsEarned,
                'expires_at' => now()->addMinutes(self::ORDER_EXPIRY_MINUTES),
            ]);

            // 8. Fire event
            event(new OrderCreated($order));

            // 9. Log
            Log::info('Order created', [
                'order_id' => $order->id,
                'order_number' => $orderNumber,
                'user_id' => $dto->userId,
                'event_id' => $dto->eventId,
                'quantity' => $dto->quantity,
                'total' => $total,
            ]);

            return $order->load('event');
        });
    }

    /**
     * Complete order after successful payment
     */
    public function completeOrder(Order $order, PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($order, $transaction) {
            // 1. Get ticket type
            $ticketType = EventTicketType::lockForUpdate()->findOrFail($order->event_id);

            // 2. Confirm sale (reserved → sold)
            $ticketType->confirmSale($order->quantity);

            // 3. Update order
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'payment_transaction_id' => $transaction->id,
            ]);

            // 4. Award points to user
            $order->user->increment('points', $order->points_earned);

            // 5. Generate tickets
            $this->ticketService->generateTickets($order);

            // 6. Update event stats
            $order->event->increment('tickets_sold', $order->quantity);

            // 7. Mark email as ready to send
            $order->update(['ticket_email_sent_at' => null]); // Will be sent by job

            // 8. Fire event
            event(new OrderCompleted($order));

            // 9. Log
            Log::info('Order completed', [
                'order_id' => $order->id,
                'transaction_id' => $transaction->id,
                'points_earned' => $order->points_earned,
            ]);
        });
    }

    /**
     * Cancel order and release reserved tickets
     */
    public function cancelOrder(int $orderId, string $reason): void
    {
        DB::transaction(function () use ($orderId, $reason) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->status !== 'pending_payment') {
                throw ValidationException::withMessages([
                    'order' => ['Sadece bekleyen siparişler iptal edilebilir!']
                ]);
            }

            // Release reserved tickets
            $ticketType = EventTicketType::lockForUpdate()->findOrFail($order->ticket_type_id);
            $ticketType->releaseReserved($order->quantity);

            // Update order
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Fire event
            event(new OrderCancelled($order, $reason));

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Expire order (called by scheduled job)
     */
    public function expireOrder(int $orderId): void
    {
        $this->cancelOrder($orderId, 'Order expired - payment not completed within time limit');
    }

    /**
     * Get user's orders
     */
    public function getUserOrders(int $userId): array
    {
        return Order::where('user_id', $userId)
            ->with(['event', 'tickets'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Validate quantity limits
     */
    private function validateQuantity(EventTicketType $ticketType, int $quantity): void
    {
        if ($quantity < $ticketType->min_per_order) {
            throw ValidationException::withMessages([
                'quantity' => ["Minimum {$ticketType->min_per_order} adet alınmalı!"]
            ]);
        }

        if ($ticketType->max_per_order && $quantity > $ticketType->max_per_order) {
            throw ValidationException::withMessages([
                'quantity' => ["Maksimum {$ticketType->max_per_order} adet alınabilir!"]
            ]);
        }

        if ($quantity > $ticketType->available) {
            throw ValidationException::withMessages([
                'quantity' => ["Sadece {$ticketType->available} adet bilet kaldı!"]
            ]);
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
