<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Services;

use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\Transfer\DTOs\CreateTransferDTO;
use App\Modules\Transfer\Enums\TransferStatus;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketTransferService
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function createTransfer(User $seller, CreateTransferDTO $dto): TicketTransfer
    {
        return DB::transaction(function () use ($seller, $dto) {

            // 1. ADIM: Bileti bul ve KİLİTLE (Pessimistic Locking)
            // 'lockForUpdate': Bu işlem bitene kadar başka kimse bu satırı okuyamaz/yazamaz.
            // Bu, "Concurrent Transfer Attempt" riskini %100 engeller.
            /** @var Ticket $ticket */
            $ticket = Ticket::where('id', $dto->ticketId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. ADIM: Güvenlik Kontrolleri

            // A) Bilet senin mi?
            if ($ticket->user_id !== $seller->id) {
                throw ValidationException::withMessages(['ticket_id' => 'Bu bilet size ait değil.']);
            }

            // B) Bilet aktif mi? (Kullanılmış veya iptal edilmiş mi?)
            if ($ticket->status !== TicketStatus::ACTIVE) {
                throw ValidationException::withMessages(['ticket_id' => 'Sadece aktif biletler transfer edilebilir.']);
            }

            // C) Bilet şu an kilitli mi? (Başka bir işlemde mi?)
            if ($ticket->is_locked) {
                throw ValidationException::withMessages(['ticket_id' => 'Bu bilet şu an başka bir işlemde (Transfer/Satış).']);
            }

            // D) Zincirleme Transfer Kontrolü (Chain Transfer Prevention)
            // Dokümanda: "Bir bilet sadece 1 kez transfer edilebilir" kuralı.
            if ($ticket->is_transferred) {
                throw ValidationException::withMessages(['ticket_id' => 'Bu bilet daha önce transfer edilmiş. Tekrar transfer edilemez.']);
            }

            // E) Fiyat Kontrolü (Karaborsa Önleme)
            // Satış fiyatı, orijinal bilet fiyatından yüksek olamaz.
            // Ticket -> EventTicketType -> Price ilişkisinden fiyatı alıyoruz.
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
            // Komisyon hesaplama (Örn: %15 platform/mekan payı)
            // Şimdilik basit tutuyoruz, ileride Venue ayarlarından çekeceğiz.
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
                'status' => TransferStatus::PENDING_VENUE_APPROVAL, // İlk aşama: Mekan onayı
                'expires_at' => now()->addHours(48), // 48 saat içinde onaylanmazsa düşer
            ]);

            // 5. ADIM: Bileti Kilitle
            // Artık bilet üzerinde başka işlem yapılamaz (Check-in vb.)
            $ticket->update([
                'is_locked' => true,
                'locked_reason' => 'Transfer süreci başlatıldı',
            ]);

            return $transfer;
        });
    }

    public function approveByVenue(User $user, TicketTransfer $transfer): TicketTransfer
    {
        // 1. Yetki Kontrolü: Onaylayan kişi, etkinliğin yapıldığı mekanın sahibi mi?
        // İlişki Zinciri: Transfer -> Ticket -> Event -> Venue -> Owner(User)
        $venueOwnerId = $transfer->ticket->event->venue->user_id;

        if ($user->id !== $venueOwnerId) {
            throw ValidationException::withMessages(['error' => 'Bu transferi onaylama yetkiniz yok.']);
        }

        // 2. Statü Kontrolü: Sadece 'pending_venue_approval' olanlar onaylanabilir.
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

    public function acceptByBuyer(User $user, TicketTransfer $transfer): TicketTransfer
    {
        // 1. Yetki Kontrolü: İşlemi yapan kişi, transferin hedefindeki alıcı mı?
        if ($user->id !== $transfer->to_user_id) {
            throw ValidationException::withMessages(['error' => 'Bu transfer size gönderilmemiş.']);
        }

        // 2. Statü Kontrolü: Sadece 'pending_buyer_acceptance' olanlar kabul edilebilir.
        if ($transfer->status !== TransferStatus::PENDING_BUYER_ACCEPTANCE) {
            throw ValidationException::withMessages(['error' => 'Bu transfer kabul edilmeye uygun değil.']);
        }

        // 3. Zaman Aşımı Kontrolü
        if ($transfer->expires_at && $transfer->expires_at->isPast()) {
            $transfer->update(['status' => TransferStatus::REJECTED]); // Expire oldu
            throw ValidationException::withMessages(['error' => 'Transfer teklifinin süresi dolmuş.']);
        }

        // 4. Güncelleme -> Ödeme Bekleniyor
        $transfer->update([
            'status' => TransferStatus::PENDING_PAYMENT,
        ]);

        return $transfer;
    }
}
