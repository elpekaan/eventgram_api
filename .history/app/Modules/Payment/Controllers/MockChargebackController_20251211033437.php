<?php

declare(strict_types=1);

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Payment\Services\ChargebackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockChargebackController extends Controller
{
    public function __construct(
        protected ChargebackService $chargebackService
    ) {}

    public function trigger(Request $request): JsonResponse
    {
        // Admin yetkisi veya Webhook secret kontrolü olmalı.
        // Test için basit tutuyoruz.

        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'exists:payment_transactions,transaction_id'],
        ]);

        $transaction = PaymentTransaction::where('transaction_id', $validated['transaction_id'])->firstOrFail();

        // Bankadan gelmiş gibi veri uyduruyoruz
        $mockBankData = [
            'amount' => $transaction->amount,
            'reason_code' => 'FRAUD_01',
            'reason_description' => 'Cardholder does not recognize this transaction',
        ];

        $chargeback = $this->chargebackService->handleDispute($transaction, $mockBankData);

        return response()->json([
            'message' => 'Chargeback processed. Account frozen.',
            'data' => $chargeback,
        ]);
    }
}
