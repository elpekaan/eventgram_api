<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Services;

use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\Ticket\Services\TicketService;
use App\Modules\Transfer\DTOs\CreateTransferDTO;
use App\Modules\Transfer\Enums\TransferStatus;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Models\TransferPayout;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketTransferService
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * Transfer sürecini başlatır.
     * Bilet kilitlenir ve transfer kaydı oluşturulur.
     */
    public function createTransfer(User $seller, CreateTransferDTO $dto): TicketTransfer
    {
        return DB::transaction(function () use ($seller, $dto) {

            // 1. ADIM: Bileti bul ve KİLİTLE (Pessimistic Locking)
            /** @var Ticket $ticket */
            $ticket = Ticket::where('id', $dto->ticketId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. ADIM: Güvenlik Kontrolleri

            // A) Bilet senin mi?
            if ($ticket->user_id !== $seller->id) {
                throw ValidationException::withMessages(['ticket_id' => 'Bu bilet size ait değil.']);
            }

            // B) Bilet aktif mi?
            if ($ticket->status !== TicketStatus::ACTIVE) {
                throw ValidationException::withMessages(['ticket_id' => 'Sadece aktif biletler transfer edilebilir.']);
            }

            // C) Bilet şu an kilitli mi?
            if ($ticket->is_locked) {
                throw ValidationException::withMessages(['ticket_id' => 'Bu bilet şu an başka bir işlemde (Transfer/Satış).']);
            }

            // D) Zincirleme Transfer Kontrolü
            if ($ticket->is_transferred) {
                throw ValidationException::withMessages(['ticket_id' => 'Bu bilet daha önce transfer edilmiş. Tekrar transfer edilemez.']);
            }

            // E) Fiyat Kontrolü
            $originalPrice = $ticket->ticketType->price;
            if ($dto->askingPrice > $originalPrice) {
                throw ValidationException::withMessages(['asking_price' => "Maksimum transfer fiyatı {$originalPrice}₺ olabilir."]);
            }

            // 3. ADIM: Alıcıyı Bul
            $buyer = User::where('email', $dto->buyerEmail)->firstOrFail();
            if ($buyer->id === $seller->id) {
                throw ValidationException::withMessages(['buyer_email' => 'Kendinize transfer yapamazsınız.']);
            }

            // 4. ADIM: Transfer Kaydını Oluştur
            $commissionRate = 0.15;
            $commissionAmount = $dto->askingPrice * $commissionRate;
            $sellerReceives = $dto->askingPrice - $commissionAmount;

            $transfer = TicketTransfer::create([
                'ticket_id' => $ticket->id,
                'from_user_id' => $seller->id,
                'to_user_id' => $buyer->id,
                'asking_price' => $dto->askingPrice,
                'platform_commission' => $commissionAmount,
                'seller_receives' => $sellerReceives,
                'status' => TransferStatus::PENDING_VENUE_APPROVAL,
                'expires_at' => now()->addHours(48),
            ]);

            // 5. ADIM: Bileti Kilitle
            $ticket->update([
                'is_locked' => true,
                'locked_reason' => 'Transfer süreci başlatıldı',
            ]);

            return $transfer;
        });
    }

    /**
     * Mekan sahibi transferi onaylar.
     */
    public function approveByVenue(User $user, TicketTransfer $transfer): TicketTransfer
    {
        // 1. Yetki Kontrolü
        $venueOwnerId = $transfer->ticket->event->venue->user_id;

        if ($user->id !== $venueOwnerId) {
            throw ValidationException::withMessages(['error' => 'Bu transferi onaylama yetkiniz yok.']);
        }

        // 2. Statü Kontrolü
        if ($transfer->status !== TransferStatus::PENDING_VENUE_APPROVAL) {
            throw ValidationException::withMessages(['error' => 'Bu transfer onaylanmaya uygun değil.']);
        }

        // 3. Güncelleme
        $transfer->update([
            'status' => TransferStatus::PENDING_BUYER_ACCEPTANCE,
            'venue_approved_at' => now(),
        ]);

        return $transfer;
    }

    /**
     * Alıcı transferi kabul eder (Ödeme öncesi son adım).
     */
    public function acceptByBuyer(User $user, TicketTransfer $transfer): TicketTransfer
    {
        // 1. Yetki Kontrolü
        if ($user->id !== $transfer->to_user_id) {
            throw ValidationException::withMessages(['error' => 'Bu transfer size gönderilmemiş.']);
        }

        // 2. Statü Kontrolü
        if ($transfer->status !== TransferStatus::PENDING_BUYER_ACCEPTANCE) {
            throw ValidationException::withMessages(['error' => 'Bu transfer kabul edilmeye uygun değil.']);
        }

        // 3. Zaman Aşımı Kontrolü
        if ($transfer->expires_at && $transfer->expires_at->isPast()) {
            $transfer->update(['status' => TransferStatus::REJECTED]);
            throw ValidationException::withMessages(['error' => 'Transfer teklifinin süresi dolmuş.']);
        }

        // 4. Güncelleme -> Ödeme Bekleniyor
        $transfer->update([
            'status' => TransferStatus::PENDING_PAYMENT,
        ]);

        return $transfer;
    }

    /**
     * Ödeme başarıyla tamamlandığında çağrılır.
     * Bilet sahipliğini değiştirir ve QR kodunu yeniler.
     */
    public function completeTransfer(TicketTransfer $transfer, PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($transfer, $transaction) {
            // 1. Transfer Statüsünü ve Transaction'ı Güncelle
            $transfer->update([
                'status' => TransferStatus::COMPLETED,
                'payment_transaction_id' => $transaction->id,
                'completed_at' => now(),
            ]);

            // 2. Bilet Sahipliğini Değiştir ve Kilidi Aç
            $ticket = $transfer->ticket;
            $ticket->update([
                'user_id' => $transfer->to_user_id, // Yeni Sahip
                'is_transferred' => true,
                'is_locked' => false,
                'locked_reason' => null,
                'transferred_at' => now(),
                'transferred_from' => $transfer->from_user_id,
            ]);

            // 3. QR Kodunu Yenile (Güvenlik)
            $this->ticketService->regenerateQrCode($ticket);

            // 4. Satıcıya Ödeme Kaydı Oluştur
            TransferPayout::create([
                'transfer_id' => $transfer->id,
                'seller_id' => $transfer->from_user_id,
                'amount' => $transfer->seller_receives,
                'status' => 'pending',
            ]);
        });
    }
}
