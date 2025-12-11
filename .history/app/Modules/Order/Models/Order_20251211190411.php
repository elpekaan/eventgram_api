<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Modules\Event\Models\Event;
use App\Modules\User\Models\User;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\Payment\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property string $order_number
 * @property float $subtotal
 * @property float $service_fee
 * @property float $total
 * @property string|null $coupon_code
 * @property float $discount
 * @property int|null $payment_transaction_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $refund_requested_at
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $chargeback_at
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $ticket_email_sent_at
 * @property bool $ticket_email_opened
 * @property int $points_earned
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read Event $event
 * @property-read PaymentTransaction|null $paymentTransaction
 * @property-read \Illuminate\Database\Eloquent\Collection<Ticket> $tickets
 * @property-read bool $is_completed
 * @property-read bool $is_cancelled
 * @property-read bool $is_refunded
 * @property-read bool $is_expired
 * @property-read int $ticket_count
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'order_number',
        'subtotal',
        'service_fee',
        'total',
        'coupon_code',
        'discount',
        'payment_transaction_id',
        'status',
        'completed_at',
        'cancelled_at',
        'refund_requested_at',
        'refunded_at',
        'chargeback_at',
        'ip_address',
        'user_agent',
        'ticket_email_sent_at',
        'ticket_email_opened',
        'points_earned',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'discount' => 'decimal:2',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refund_requested_at' => 'datetime',
            'refunded_at' => 'datetime',
            'chargeback_at' => 'datetime',
            'ticket_email_sent_at' => 'datetime',
            'ticket_email_opened' => 'boolean',
            'points_earned' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Order owner (buyer)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order's event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Order's payment transaction
     */
    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Order's tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Only completed orders
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Only pending orders
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending_payment');
    }

    /**
     * Scope: Expired orders (pending + past expiry)
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'pending_payment')
            ->where('expires_at', '<', now());
    }

    /**
     * Scope: Orders for a specific event
     */
    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope: Orders within date range
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Check if order is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is cancelled
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order is refunded
     */
    public function getIsRefundedAttribute(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if order is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'pending_payment'
            && $this->expires_at
            && $this->expires_at < now();
    }

    /**
     * Get ticket count
     */
    public function getTicketCountAttribute(): int
    {
        return $this->tickets()->count();
    }

    /**
     * Get formatted order number
     */
    public function getFormattedOrderNumberAttribute(): string
    {
        return strtoupper($this->order_number);
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Mark order as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark order as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Mark order as refunded
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->is_completed && !$this->is_refunded;
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
    }
}
