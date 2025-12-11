<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Transfer\Enums\TransferStatus;
use App\Modules\Transfer\Models\TicketTransfer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleTransferTimeouts extends Command
{
    /**
     * Komutun terminaldeki adı
     */
    protected $signature = 'transfers:handle-timeouts';

    /**
     * Açıklaması
     */
    protected $description = 'Süresi dolan transferleri otomatik olarak iptal eder';

    public function handle(): void
    {
        $this->info('Zaman aşımı kontrolü başlıyor...');

        // 1. MEKAN ONAYI BEKLEYENLER (48 Saat)
        $this->handleVenueTimeouts();

        // 2. ALICI KABULÜ BEKLEYENLER (72 Saat)
        $this->handleBuyerTimeouts();

        // 3. ÖDEME BEKLEYENLER (10 Dakika)
        $this->handlePaymentTimeouts();

        $this->info('Kontrol tamamlandı.');
    }

    private function handleVenueTimeouts(): void
    {
        $expired = TicketTransfer::where('status', TransferStatus::PENDING_VENUE_APPROVAL)
            ->where('created_at', '<', now()->subHours(48))
            ->get();

        foreach ($expired as $transfer) {
            $this->expireTransfer($transfer, 'Mekan 48 saat içinde yanıt vermedi.');
        }
    }

    private function handleBuyerTimeouts(): void
    {
        $expired = TicketTransfer::where('status', TransferStatus::PENDING_BUYER_ACCEPTANCE)
            ->where('venue_approved_at', '<', now()->subHours(72))
            ->get();

        foreach ($expired as $transfer) {
            $this->expireTransfer($transfer, 'Alıcı 72 saat içinde kabul etmedi.');
        }
    }

    private function handlePaymentTimeouts(): void
    {
        $expired = TicketTransfer::where('status', TransferStatus::PENDING_PAYMENT)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $transfer) {
            $this->expireTransfer($transfer, 'Ödeme süresi doldu.');
        }
    }

    private function expireTransfer(TicketTransfer $transfer, string $reason): void
    {
        DB::transaction(function () use ($transfer, $reason) {
            $transfer->update([
                'status' => TransferStatus::FAILED,
                'rejection_reason' => "Timeout: $reason",
            ]);

            $transfer->ticket->update([
                'is_locked' => false,
                'locked_reason' => null,
            ]);

            Log::info("Transfer #{$transfer->id} zaman aşımına uğradı: $reason");
        });
    }
}
