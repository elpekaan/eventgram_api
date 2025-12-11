<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $transfer_id
 * @property int $seller_id
 * @property float $amount
 * @property string|null $iban
 * @property string $status
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property int|null $processed_by
 * @property string|null $provider_payout_id
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read TicketTransfer $transfer
 * @property-read User $seller
 * @property-read User|null $processor
 */
class TransferPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'seller_id',
        'amount',
        'iban',
        'status',
        'requested_at',
        'processed_at',
        'processed_by',
        'provider_payout_id',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(TicketTransfer::class, 'transfer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    // ========================================
    // METHODS
    // ========================================

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(int $adminId, string $providerPayoutId): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'processed_by' => $adminId,
            'provider_payout_id' => $providerPayoutId,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }
}
