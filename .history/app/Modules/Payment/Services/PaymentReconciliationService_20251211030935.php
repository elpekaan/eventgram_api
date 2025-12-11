<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Payment\Models\ReconciliationReport;
use Carbon\Carbon;

class PaymentReconciliationService
{
    public function reconcileDate(Carbon $date): ReconciliationReport
    {
        // 1. Bizim Verilerimiz (Platform)
        // O günkü başarılı işlemleri çekiyoruz
        $platformTransactions = PaymentTransaction::whereDate('created_at', $date)
            ->where('status', 'success')
            ->get();

        $platformTotal = $platformTransactions->sum('amount');
        $platformCount = $platformTransactions->count();

        // 2. İyzico Verileri (Provider)
        // Normalde: $iyzico->getSettlement($date);
        // MOCK: Bizim verimizle birebir aynıymış gibi (veya küçük farkla) simüle edelim.
        $providerData = $this->mockProviderData($platformTransactions);

        $providerTotal = $providerData['total_amount'];
        $providerCount = $providerData['count'];

        // 3. Karşılaştırma
        $diff = abs($platformTotal - $providerTotal);
        $status = $diff < 0.01 ? 'matched' : 'mismatch';

        // 4. Raporu Kaydet
        return ReconciliationReport::create([
            'date' => $date,
            'platform_count' => $platformCount,
            'platform_total' => $platformTotal,
            'provider_count' => $providerCount,
            'provider_total' => $providerTotal,
            'status' => $status,
            'difference' => $diff,
            'discrepancies' => $status === 'mismatch' ? ['note' => 'Simulated mismatch'] : null,
        ]);
    }

    private function mockProviderData($transactions): array
    {
        // Test amaçlı: Bazen tutsun, bazen tutmasın diye random yapabiliriz.
        // Ama şimdilik "Her şey yolunda" senaryosu:
        return [
            'total_amount' => $transactions->sum('amount'),
            'count' => $transactions->count(),
        ];
    }
}
