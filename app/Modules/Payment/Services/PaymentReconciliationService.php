<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Contracts\Services\PaymentReconciliationServiceInterface;
use App\Modules\Payment\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentReconciliationService implements PaymentReconciliationServiceInterface
{
    public function reconcileDate(Carbon $date): array
    {
        // Platform transactions
        $platformTransactions = PaymentTransaction::whereDate('created_at', $date)
            ->where('status', 'success')
            ->get();

        $platformTotal = $platformTransactions->sum('amount');
        $platformCount = $platformTransactions->count();

        // Provider data (mocked)
        $providerData = $this->mockProviderData($platformTransactions);
        $providerTotal = $providerData['total_amount'];
        $providerCount = $providerData['count'];

        // Compare
        $diff = abs($platformTotal - $providerTotal);
        $status = $diff < 0.01 ? 'matched' : 'mismatch';

        Log::info('Payment reconciliation completed', [
            'date' => $date->toDateString(),
            'status' => $status,
            'platform_total' => $platformTotal,
            'provider_total' => $providerTotal,
        ]);

        return [
            'date' => $date->toDateString(),
            'platform_count' => $platformCount,
            'platform_total' => $platformTotal,
            'provider_count' => $providerCount,
            'provider_total' => $providerTotal,
            'status' => $status,
            'difference' => $diff,
        ];
    }

    private function mockProviderData($transactions): array
    {
        return [
            'total_amount' => $transactions->sum('amount'),
            'count' => $transactions->count(),
        ];
    }
}
