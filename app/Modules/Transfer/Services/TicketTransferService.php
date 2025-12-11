<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Services;

use App\Contracts\Services\TicketTransferServiceInterface;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\Ticket\Services\TicketService;
use App\Modules\Transfer\DTOs\CreateTransferDTO;
use App\Modules\Transfer\Events\TransferCreated;
use App\Modules\Transfer\Events\TransferCompleted;
use App\Modules\Transfer\Events\TransferCancelled;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Models\TransferPayout;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TicketTransferService implements TicketTransferServiceInterface
{
    private const COMMISSION_RATE = 0.15;
    private const TRANSFER_EXPIRY_HOURS = 48;

    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * Create transfer listing
     */
    public function createTransfer(User $seller, CreateTransferDTO $dto): TicketTransfer
    {
        return DB::transaction(function () use ($seller, $dto) {
            // 1. Find and lock ticket
            $ticket = Ticket::where('id', $dto->ticketId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Validate transfer eligibility
            $this->validateTransferEligibility($ticket, $seller);

            // 3. Validate price
            $this->validatePrice($ticket, $dto->askingPrice);

            // 4. Find buyer
            $buyer = User::where('email', $dto->buyerEmail)->firstOrFail();
            
            if ($buyer->id === $seller->id) {
                throw ValidationException::withMessages([
                    'buyer_email' => ['Kendinize transfer yapamazsınız.']
                ]);
            }

            // 5. Calculate pricing
            $commissionAmount = $dto->askingPrice * self::COMMISSION_RATE;
            $sellerReceives = $dto->askingPrice - $commissionAmount;

            // 6. Create transfer
            $transfer = TicketTransfer::create([
                'ticket_id' => $ticket->id,
                'from_user_id' => $seller->id,
                'to_user_id' => $buyer->id,
                'asking_price' => $dto->askingPrice,
                'platform_commission' => $commissionAmount,
                'seller_receives' => $sellerReceives,
                'status' => 'pending_venue_approval',
                'escrow_status' => 'none',
                'expires_at' => now()->addHours(self::TRANSFER_EXPIRY_HOURS),
            ]);

            // 7. Lock ticket
            $ticket->lock('Transfer süreci başlatıldı');

            // 8. Fire event
            event(new TransferCreated($transfer));

            // 9. Log
            Log::info('Transfer created', [
                'transfer_id' => $transfer->id,
                'seller_id' => $seller->id,
                'buyer_id' => $buyer->id,
                'ticket_id' => $ticket->id,
                'price' => $dto->askingPrice,
            ]);

            return $transfer->load('ticket.event', 'seller', 'buyer');
        });
    }

    /**
     * Venue approves transfer
     */
    public function approveByVenue(User $user, TicketTransfer $transfer): TicketTransfer
    {
        return DB::transaction(function () use ($user, $transfer) {
            // Validate authorization
            $this->validateVenueAuthorization($user, $transfer);

            // Validate status
            if ($transfer->status !== 'pending_venue_approval') {
                throw ValidationException::withMessages([
                    'error' => ['Bu transfer onaylanmaya uygun değil.']
                ]);
            }

            // Approve
            $transfer->approve();

            Log::info('Transfer approved by venue', [
                'transfer_id' => $transfer->id,
                'venue_owner_id' => $user->id,
            ]);

            return $transfer;
        });
    }

    /**
     * Venue rejects transfer
     */
    public function rejectByVenue(User $user, TicketTransfer $transfer, string $reason): TicketTransfer
    {
        return DB::transaction(function () use ($user, $transfer, $reason) {
            // Validate authorization
            $this->validateVenueAuthorization($user, $transfer);

            // Reject
            $transfer->reject($reason);

            // Unlock ticket
            $transfer->ticket->unlock();

            Log::info('Transfer rejected by venue', [
                'transfer_id' => $transfer->id,
                'reason' => $reason,
            ]);

            return $transfer;
        });
    }

    /**
     * Buyer accepts transfer
     */
    public function acceptByBuyer(User $user, TicketTransfer $transfer): TicketTransfer
    {
        return DB::transaction(function () use ($user, $transfer) {
            // Validate authorization
            if ($user->id !== $transfer->to_user_id) {
                throw ValidationException::withMessages([
                    'error' => ['Bu transfer size gönderilmemiş.']
                ]);
            }

            // Validate status
            if ($transfer->status !== 'listed') {
                throw ValidationException::withMessages([
                    'error' => ['Bu transfer kabul edilmeye uygun değil.']
                ]);
            }

            // Check expiry
            if ($transfer->expires_at && $transfer->expires_at->isPast()) {
                $transfer->update(['status' => 'expired']);
                throw ValidationException::withMessages([
                    'error' => ['Transfer teklifinin süresi dolmuş.']
                ]);
            }

            // Accept
            $transfer->update(['status' => 'pending_payment']);

            Log::info('Transfer accepted by buyer', [
                'transfer_id' => $transfer->id,
                'buyer_id' => $user->id,
            ]);

            return $transfer;
        });
    }

    /**
     * Complete transfer after payment
     */
    public function completeTransfer(TicketTransfer $transfer, PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($transfer, $transaction) {
            // 1. Hold payment in escrow
            $transfer->holdEscrow();

            // 2. Update transfer
            $transfer->update([
                'status' => 'completed',
                'payment_transaction_id' => $transaction->id,
                'completed_at' => now(),
            ]);

            // 3. Transfer ticket ownership
            $ticket = $transfer->ticket;
            $ticket->update([
                'user_id' => $transfer->to_user_id,
                'is_transferred' => true,
                'transfer_completed' => true,
                'is_locked' => false,
                'locked_reason' => null,
                'transferred_at' => now(),
                'transferred_from' => $transfer->from_user_id,
                'status' => 'active',
            ]);

            // 4. Regenerate QR code
            $this->ticketService->regenerateQrCode($ticket);

            // 5. Create payout record
            TransferPayout::create([
                'transfer_id' => $transfer->id,
                'seller_id' => $transfer->from_user_id,
                'amount' => $transfer->seller_receives,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // 6. Release escrow
            $transfer->releaseEscrow();

            // 7. Fire event
            event(new TransferCompleted($transfer));

            // 8. Log
            Log::info('Transfer completed', [
                'transfer_id' => $transfer->id,
                'transaction_id' => $transaction->id,
                'new_owner_id' => $transfer->to_user_id,
            ]);
        });
    }

    /**
     * Cancel transfer
     */
    public function cancelTransfer(TicketTransfer $transfer, string $reason): void
    {
        DB::transaction(function () use ($transfer, $reason) {
            $transfer->cancel($transfer->from_user_id, $reason);
            
            // Unlock ticket
            $transfer->ticket->unlock();

            // Fire event
            event(new TransferCancelled($transfer, $reason));

            Log::info('Transfer cancelled', [
                'transfer_id' => $transfer->id,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Validate transfer eligibility
     */
    private function validateTransferEligibility(Ticket $ticket, User $seller): void
    {
        if ($ticket->user_id !== $seller->id) {
            throw ValidationException::withMessages([
                'ticket_id' => ['Bu bilet size ait değil.']
            ]);
        }

        if ($ticket->status !== 'active') {
            throw ValidationException::withMessages([
                'ticket_id' => ['Sadece aktif biletler transfer edilebilir.']
            ]);
        }

        if ($ticket->is_locked) {
            throw ValidationException::withMessages([
                'ticket_id' => ['Bu bilet şu an başka bir işlemde.']
            ]);
        }

        if ($ticket->transfer_completed) {
            throw ValidationException::withMessages([
                'ticket_id' => ['Bu bilet daha önce transfer edilmiş.']
            ]);
        }
    }

    /**
     * Validate price
     */
    private function validatePrice(Ticket $ticket, float $askingPrice): void
    {
        $originalPrice = $ticket->ticketType->price;
        
        if ($askingPrice > $originalPrice) {
            throw ValidationException::withMessages([
                'asking_price' => ["Maksimum transfer fiyatı {$originalPrice}₺ olabilir."]
            ]);
        }
    }

    /**
     * Validate venue authorization
     */
    private function validateVenueAuthorization(User $user, TicketTransfer $transfer): void
    {
        $venueOwnerId = $transfer->ticket->event->venue->user_id;

        if ($user->id !== $venueOwnerId) {
            throw ValidationException::withMessages([
                'error' => ['Bu transferi onaylama yetkiniz yok.']
            ]);
        }
    }
}
