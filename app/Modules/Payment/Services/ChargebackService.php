<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Payment\Models\Chargeback;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Ticket\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChargebackService
{
    public function handleDispute(PaymentTransaction $transaction, array $bankData): Chargeback
    {
        return DB::transaction(function () use ($transaction, $bankData) {
            $order = $transaction->order;

            // 1. Chargeback Kaydı Oluştur
            $chargeback = Chargeback::create([
                'payment_transaction_id' => $transaction->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $bankData['amount'] ?? $transaction->amount,
                'reason_code' => $bankData['reason_code'] ?? 'UNKNOWN',
                'reason_description' => $bankData['reason_description'] ?? 'Bank dispute received',
                'dispute_date' => now(),
                'status' => 'received',
                'evidence_snapshot' => [
                    'ip_address' => '127.0.0.1', // Loglardan çekilmeli
                    'ticket_download_count' => 0, // İleride eklenecek
                    'user_history' => 'Clean',
                ]
            ]);

            // 2. Siparişi "Chargeback" statüsüne çek
            $order->update(['status' => OrderStatus::CHARGEBACK]);

            // 3. Biletleri Blokla (İçeri giremesinler!)
            $order->tickets()->update(['status' => TicketStatus::BLOCKED]);

            // 4. Admin Logu
            Log::critical("CHARGEBACK ALARMI! Order #{$order->id} için itiraz geldi. Biletler bloklandı.");

            return $chargeback;
        });
    }
}
