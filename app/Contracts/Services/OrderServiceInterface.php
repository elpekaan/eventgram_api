<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Order\DTOs\CreateOrderDTO;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\PaymentTransaction;

interface OrderServiceInterface
{
    public function createOrder(CreateOrderDTO $dto): Order;
    
    public function completeOrder(Order $order, PaymentTransaction $transaction): void;
    
    public function cancelOrder(int $orderId, string $reason): void;
    
    public function expireOrder(int $orderId): void;
    
    public function getUserOrders(int $userId): array;
}
