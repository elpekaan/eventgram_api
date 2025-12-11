<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Payment\Services\PaymentReconciliationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {date? : YYYY-MM-DD formatında tarih}';
    protected $description = 'Günlük ödeme mutabakatını çalıştırır';

    public function handle(PaymentReconciliationService $service): void
    {
        // Tarih verilmezse "Dün"ü baz al
        $dateInput = $this->argument('date');
        $date = $dateInput ? Carbon::parse($dateInput) : now(); // Test için 'bugün' yapalım, normalde 'subDay()' olur.

        $this->info("Mutabakat başlatılıyor: {$date->format('Y-m-d')}");

        $report = $service->reconcileDate($date);

        $this->table(
            ['Metrik', 'Değer'],
            [
                ['Platform Toplam', $report->platform_total],
                ['İyzico Toplam', $report->provider_total],
                ['Fark', $report->difference],
                ['Durum', $report->status],
            ]
        );

        if ($report->status === 'matched') {
            $this->info('✅ Mutabakat Başarılı!');
        } else {
            $this->error('❌ Uyuşmazlık Var!');
        }
    }
}
