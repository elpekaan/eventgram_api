<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Refund;

interface RefundServiceInterface
{
    public function createRefundRequest(Order $order, string $reason, string $description): Refund;
    
    public function approveRefund(int $refundId, int $adminId, string $notes): Refund;
    
    public function rejectRefund(int $refundId, int $adminId, string $reason): Refund;
    
    public function processRefund(Refund $refund): void;
    
    public function getPendingRefunds(): array;
}
