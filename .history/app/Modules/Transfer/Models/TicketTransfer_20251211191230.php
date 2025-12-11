<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Models;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\User\Models\User;
use App\Modules\Payment\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $from_user_id
 * @property int|null $to_user_id
 * @property float $asking_price
 * @property float $platform_commission
 * @property float $seller_receives
 * @property string $status
 * @property string $escrow_status
 * @property \Illuminate\Support\Carbon|null $venue_approved_at
 * @property string|null $rejection_reason
 * @property int|null $payment_transaction_id
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int|null $cancelled_by
 * @property string|null $cancellation_reason
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Ticket $ticket
 * @property-read User $seller
 * @property-read User|null $buyer
 * @property-read User|null $cancelledBy
 * @property-read PaymentTransaction|null $payment
 * @property-read TransferPayout|null $payout
 * @property-read bool $is_completed
 * @property-read bool $is_cancelled
 * @property-read bool $is_expired
 */
class TicketTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'from_user_id',
        'to_user_id',
        'asking_price',
        'platform_commission',
        'seller_receives',
        'status',
        'escrow_status',
        'venue_approved_at',
        'rejection_reason',
        'payment_transaction_id',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'asking_price' => 'decimal:2',
            'platform_commission' => 'decimal:2',
            'seller_receives' => 'decimal:2',
            'venue_approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The ticket being transferred
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Seller (from_user)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Buyer (to_user)
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * User who cancelled the transfer
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Payment transaction
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    /**
     * Payout to seller
     */
    public function payout(): HasOne
    {
        return $this->hasOne(TransferPayout::class, 'transfer_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_venue_approval');
    }

    public function scopeListed(Builder $query): Builder
    {
        return $query->where('status', 'listed');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['listed', 'pending_payment', 'payment_received', 'processing']);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'expired'
            || ($this->expires_at && $this->expires_at < now());
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Approve transfer (venue approval)
     */
    public function approve(): void
    {
        $this->update([
            'status' => 'listed',
            'venue_approved_at' => now(),
        ]);
    }

    /**
     * Reject transfer
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'rejection_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'escrow_status' => 'released',
        ]);
    }

    /**
     * Cancel transfer
     */
    public function cancel(int $userId, string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
            'escrow_status' => 'refunded',
        ]);
    }

    /**
     * Hold payment in escrow
     */
    public function holdEscrow(): void
    {
        $this->update([
            'escrow_status' => 'held',
            'status' => 'payment_received',
        ]);
    }

    /**
     * Release escrow to seller
     */
    public function releaseEscrow(): void
    {
        $this->update([
            'escrow_status' => 'released',
        ]);
    }
}
