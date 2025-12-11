<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Contracts\Services\ChargebackServiceInterface;
use App\Modules\Payment\Events\ChargebackReceived;
use App\Modules\Payment\Models\Chargeback;
use App\Modules\Payment\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChargebackService implements ChargebackServiceInterface
{
    /**
     * Handle chargeback dispute from bank
     */
    public function handleDispute(PaymentTransaction $transaction, array $bankData): Chargeback
    {
        return DB::transaction(function () use ($transaction, $bankData) {
            $order = $transaction->order;

            // Create chargeback record
            $chargeback = Chargeback::create([
                'payment_transaction_id' => $transaction->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $bankData['amount'] ?? $transaction->amount,
                'reason' => $bankData['reason'] ?? 'Unknown',
                'reason_code' => $bankData['reason_code'] ?? null,
                'case_id' => $bankData['case_id'] ?? null,
                'chargeback_date' => now(),
                'status' => 'received',
            ]);

            // Update order
            $order->update([
                'status' => 'chargeback',
                'chargeback_at' => now(),
            ]);

            // Block tickets
            $order->tickets()->update([
                'status' => 'chargeback',
                'chargeback_at' => now(),
            ]);

            // Fire event
            event(new ChargebackReceived($chargeback));

            // Critical log
            Log::critical('CHARGEBACK RECEIVED', [
                'chargeback_id' => $chargeback->id,
                'order_id' => $order->id,
                'amount' => $chargeback->amount,
                'reason' => $chargeback->reason,
            ]);

            return $chargeback;
        });
    }

    /**
     * Submit evidence to fight chargeback
     */
    public function submitEvidence(int $chargebackId, array $evidence, string $notes): Chargeback
    {
        return DB::transaction(function () use ($chargebackId, $evidence, $notes) {
            $chargeback = Chargeback::findOrFail($chargebackId);
            
            $chargeback->submitDispute($evidence, $notes);

            Log::info('Chargeback evidence submitted', [
                'chargeback_id' => $chargeback->id,
            ]);

            return $chargeback;
        });
    }

    /**
     * Mark chargeback as won
     */
    public function markAsWon(int $chargebackId, string $notes): Chargeback
    {
        return DB::transaction(function () use ($chargebackId, $notes) {
            $chargeback = Chargeback::findOrFail($chargebackId);
            
            $chargeback->markAsWon($notes);

            // Restore order
            $chargeback->order->update(['status' => 'completed']);

            // Restore tickets
            $chargeback->order->tickets()->update(['status' => 'active']);

            Log::info('Chargeback won', [
                'chargeback_id' => $chargeback->id,
            ]);

            return $chargeback;
        });
    }

    /**
     * Mark chargeback as lost
     */
    public function markAsLost(int $chargebackId, string $notes): Chargeback
    {
        return DB::transaction(function () use ($chargebackId, $notes) {
            $chargeback = Chargeback::findOrFail($chargebackId);
            
            $chargeback->markAsLost($notes);

            Log::warning('Chargeback lost', [
                'chargeback_id' => $chargeback->id,
                'amount' => $chargeback->amount,
            ]);

            return $chargeback;
        });
    }
}
