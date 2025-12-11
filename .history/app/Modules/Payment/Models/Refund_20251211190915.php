<?php

declare(strict_types=1);

namespace App\Modules\Payment\Models;

use App\Modules\Order\Models\Order;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property int $payment_transaction_id
 * @property float $requested_amount
 * @property float $processing_fee
 * @property float $refund_amount
 * @property string $reason
 * @property string|null $reason_description
 * @property string $status
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $approval_notes
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property int|null $rejected_by
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $provider_refund_id
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Order $order
 * @property-read User $user
 * @property-read PaymentTransaction $transaction
 * @property-read User|null $approvedBy
 * @property-read User|null $rejectedBy
 * @property-read bool $is_pending
 * @property-read bool $is_approved
 * @property-read bool $is_completed
 * @property-read bool $is_rejected
 */
class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_transaction_id',
        'requested_amount',
        'processing_fee',
        'refund_amount',
        'reason',
        'reason_description',
        'status',
        'requested_at',
        'approved_at',
        'approved_by',
        'approval_notes',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'completed_at',
        'provider_refund_id',
        'failed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ========================================
    // ACCESSORS
    // ========================================

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    // ========================================
    // METHODS
    // ========================================

    public function approve(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $adminId,
            'approval_notes' => $notes,
        ]);
    }

    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $adminId,
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsCompleted(string $providerRefundId): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'provider_refund_id' => $providerRefundId,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
        ]);
    }
}
