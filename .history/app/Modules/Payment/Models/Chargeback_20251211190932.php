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
 * @property int $payment_transaction_id
 * @property int $order_id
 * @property int $user_id
 * @property float $amount
 * @property string $reason
 * @property string|null $reason_code
 * @property string|null $case_id
 * @property \Illuminate\Support\Carbon $chargeback_date
 * @property string $status
 * @property array|null $dispute_evidence
 * @property \Illuminate\Support\Carbon|null $dispute_submitted_at
 * @property string|null $dispute_notes
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property string|null $resolution
 * @property string|null $resolution_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Chargeback extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_transaction_id',
        'order_id',
        'user_id',
        'amount',
        'reason',
        'reason_code',
        'case_id',
        'chargeback_date',
        'status',
        'dispute_evidence',
        'dispute_submitted_at',
        'dispute_notes',
        'resolved_at',
        'resolution',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'dispute_evidence' => 'array',
            'chargeback_date' => 'datetime',
            'dispute_submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where('status', 'received');
    }

    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', 'under_review');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereIn('status', ['won', 'lost', 'accepted']);
    }

    // ========================================
    // METHODS
    // ========================================

    public function submitDispute(array $evidence, string $notes): void
    {
        $this->update([
            'status' => 'evidence_submitted',
            'dispute_evidence' => $evidence,
            'dispute_submitted_at' => now(),
            'dispute_notes' => $notes,
        ]);
    }

    public function markAsWon(string $notes): void
    {
        $this->update([
            'status' => 'won',
            'resolution' => 'won',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function markAsLost(string $notes): void
    {
        $this->update([
            'status' => 'lost',
            'resolution' => 'lost',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }
}
