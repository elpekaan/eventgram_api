<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Contracts\Services\RefundServiceInterface;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Events\RefundRequested;
use App\Modules\Payment\Events\RefundApproved;
use App\Modules\Payment\Events\RefundCompleted;
use App\Modules\Payment\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RefundService implements RefundServiceInterface
{
    private const USER_REQUEST_FEE = 0.10; // 10% penalty
    private const EVENT_CANCELLED_FEE = 0.00; // No penalty

    /**
     * Create refund request
     */
    public function createRefundRequest(Order $order, string $reason, string $description): Refund
    {
        return DB::transaction(function () use ($order, $reason, $description) {
            // 1. Validate order can be refunded
            $this->validateRefundEligibility($order);

            // 2. Calculate amounts
            $feePercentage = ($reason === 'user_initiated') 
                ? self::USER_REQUEST_FEE 
                : self::EVENT_CANCELLED_FEE;

            $requestedAmount = $order->total;
            $processingFee = $requestedAmount * $feePercentage;
            $refundAmount = $requestedAmount - $processingFee;

            // 3. Create refund
            $refund = Refund::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_transaction_id' => $order->payment_transaction_id,
                'requested_amount' => $requestedAmount,
                'processing_fee' => $processingFee,
                'refund_amount' => $refundAmount,
                'reason' => $reason,
                'reason_description' => $description,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // 4. Update order
            $order->update(['status' => 'refund_requested']);

            // 5. Fire event
            event(new RefundRequested($refund));

            // 6. Log
            Log::info('Refund requested', [
                'refund_id' => $refund->id,
                'order_id' => $order->id,
                'amount' => $refundAmount,
                'reason' => $reason,
            ]);

            return $refund->load('order.user');
        });
    }

    /**
     * Admin approves refund
     */
    public function approveRefund(int $refundId, int $adminId, string $notes): Refund
    {
        return DB::transaction(function () use ($refundId, $adminId, $notes) {
            $refund = Refund::lockForUpdate()->findOrFail($refundId);

            // Validate status
            if ($refund->status !== 'pending') {
                throw ValidationException::withMessages([
                    'refund' => ['Sadece bekleyen iadeler onaylanabilir!']
                ]);
            }

            // Approve
            $refund->approve($adminId, $notes);

            // Fire event
            event(new RefundApproved($refund));

            // Log
            Log::info('Refund approved', [
                'refund_id' => $refund->id,
                'admin_id' => $adminId,
            ]);

            return $refund;
        });
    }

    /**
     * Admin rejects refund
     */
    public function rejectRefund(int $refundId, int $adminId, string $reason): Refund
    {
        return DB::transaction(function () use ($refundId, $adminId, $reason) {
            $refund = Refund::lockForUpdate()->findOrFail($refundId);

            // Validate status
            if ($refund->status !== 'pending') {
                throw ValidationException::withMessages([
                    'refund' => ['Sadece bekleyen iadeler reddedilebilir!']
                ]);
            }

            // Reject
            $refund->reject($adminId, $reason);

            // Restore order status
            $refund->order->update(['status' => 'completed']);

            // Log
            Log::info('Refund rejected', [
                'refund_id' => $refund->id,
                'admin_id' => $adminId,
                'reason' => $reason,
            ]);

            return $refund;
        });
    }

    /**
     * Process approved refund with payment provider
     */
    public function processRefund(Refund $refund): void
    {
        DB::transaction(function () use ($refund) {
            // Validate status
            if ($refund->status !== 'approved') {
                throw ValidationException::withMessages([
                    'refund' => ['Sadece onaylanmış iadeler işlenebilir!']
                ]);
            }

            // TODO: Call payment provider API
            // $providerRefundId = $this->paymentProvider->refund($refund);
            $providerRefundId = 'MOCK_REF_' . uniqid();

            // Update refund
            $refund->markAsCompleted($providerRefundId);

            // Update order
            $refund->order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            // Cancel tickets
            $refund->order->tickets()->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            // Fire event
            event(new RefundCompleted($refund));

            // Log
            Log::info('Refund completed', [
                'refund_id' => $refund->id,
                'provider_refund_id' => $providerRefundId,
                'amount' => $refund->refund_amount,
            ]);
        });
    }

    /**
     * Get pending refunds for admin review
     */
    public function getPendingRefunds(): array
    {
        return Refund::pending()
            ->with(['order.user', 'order.event'])
            ->orderBy('requested_at')
            ->get()
            ->toArray();
    }

    /**
     * Validate refund eligibility
     */
    private function validateRefundEligibility(Order $order): void
    {
        if (!$order->can_be_refunded) {
            throw ValidationException::withMessages([
                'order' => ['Bu sipariş iade edilemez!']
            ]);
        }

        // Check if tickets are used
        $usedTickets = $order->tickets()->where('status', 'used')->exists();
        
        if ($usedTickets) {
            throw ValidationException::withMessages([
                'order' => ['Kullanılmış biletler iade edilemez!']
            ]);
        }

        // Check if already refunded
        if ($order->status === 'refunded') {
            throw ValidationException::withMessages([
                'order' => ['Bu sipariş zaten iade edilmiş!']
            ]);
        }

        // Check if refund already exists
        $existingRefund = Refund::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved', 'processing', 'completed'])
            ->exists();

        if ($existingRefund) {
            throw ValidationException::withMessages([
                'order' => ['Bu sipariş için zaten bir iade talebi var!']
            ]);
        }
    }
}
