<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Payment\Models\Refund;
use App\Modules\Ticket\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function createRefundRequest(Order $order, string $reason): Refund
    {
        // 1. Kontroller
        if ($order->status !== OrderStatus::PAID) {
            throw ValidationException::withMessages(['order' => 'Sadece ödenmiş siparişler iade edilebilir.']);
        }

        // Biletler kullanılmış mı?
        $usedTickets = $order->tickets()->where('status', TicketStatus::USED)->exists();
        if ($usedTickets) {
            throw ValidationException::withMessages(['order' => 'Kullanılmış biletler iade edilemez.']);
        }

        return DB::transaction(function () use ($order, $reason) {
            // 2. Tutar Hesaplama (Business Logic)
            // Eğer kullanıcı keyfi iptal ediyorsa %10 kesinti, yoksa %0.
            $feePercentage = ($reason === 'user_request') ? 0.10 : 0.00;
            $processingFee = $order->total_amount * $feePercentage;
            $refundAmount = $order->total_amount - $processingFee;

            // 3. Refund Kaydı Oluştur
            $refund = Refund::create([
                'order_id' => $order->id,
                'payment_transaction_id' => $order->payment_transaction_id,
                'user_id' => $order->user_id,
                'amount' => $refundAmount,
                'processing_fee' => $processingFee,
                'status' => 'pending', // Admin onayı veya İyzico işlemi bekliyor
                'reason' => $reason,
            ]);

            // 4. Siparişi Güncelle (Refund Requested)
            // Enum'a 'REFUND_REQUESTED' eklememiz lazım ama şimdilik manuel string geçelim veya FAILED yapalım.
            // MVP için siparişi direkt 'refunded' yapmıyoruz, işlem bitince yapacağız.

            return $refund;
        });
    }

    public function processRefund(Refund $refund): void
    {
        // BURADA İYZİCO API ÇAĞRILACAK (Mockluyoruz)
        // $iyzico->refund(...)

        DB::transaction(function () use ($refund) {
            // 1. İadeyi tamamla
            $refund->update([
                'status' => 'completed',
                'completed_at' => now(),
                'provider_refund_id' => 'mock_ref_' . uniqid(),
            ]);

            // 2. Siparişi İade Edildi yap
            $refund->order->update(['status' => OrderStatus::REFUNDED]);

            // 3. Biletleri İptal Et
            $refund->order->tickets()->update(['status' => TicketStatus::CANCELLED]);
        });
    }
}
