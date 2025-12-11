<?php

declare(strict_types=1);

namespace App\Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property float $service_fee
 * @property int $quantity
 * @property int $sold
 * @property int $reserved
 * @property \Illuminate\Support\Carbon|null $sales_start
 * @property \Illuminate\Support\Carbon|null $sales_end
 * @property int $min_per_order
 * @property int|null $max_per_order
 * @property bool $is_visible
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Event $event
 * @property-read int $available
 * @property-read bool $is_available
 * @property-read bool $is_sold_out
 * @property-read float $percentage_sold
 * @property-read float $total_price
 */
class EventTicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'service_fee',
        'quantity',
        'sold',
        'reserved',
        'sales_start',
        'sales_end',
        'min_per_order',
        'max_per_order',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'quantity' => 'integer',
            'sold' => 'integer',
            'reserved' => 'integer',
            'sales_start' => 'datetime',
            'sales_end' => 'datetime',
            'min_per_order' => 'integer',
            'max_per_order' => 'integer',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Ticket type's event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Only visible ticket types
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope: Only available ticket types (not sold out)
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_visible', true)
            ->whereRaw('sold + reserved < quantity');
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeInOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order')
            ->orderBy('price');
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get available tickets (not sold or reserved)
     */
    public function getAvailableAttribute(): int
    {
        return max(0, $this->quantity - $this->sold - $this->reserved);
    }

    /**
     * Check if ticket type is available for purchase
     */
    public function getIsAvailableAttribute(): bool
    {
        // Must be visible
        if (!$this->is_visible) {
            return false;
        }

        // Must have available tickets
        if ($this->available <= 0) {
            return false;
        }

        // Check sales period
        $now = now();

        if ($this->sales_start && $now < $this->sales_start) {
            return false;
        }

        if ($this->sales_end && $now > $this->sales_end) {
            return false;
        }

        return true;
    }

    /**
     * Check if ticket type is sold out
     */
    public function getIsSoldOutAttribute(): bool
    {
        return $this->sold >= $this->quantity;
    }

    /**
     * Get percentage sold
     */
    public function getPercentageSoldAttribute(): float
    {
        if ($this->quantity === 0) {
            return 0.0;
        }

        return round(($this->sold / $this->quantity) * 100, 2);
    }

    /**
     * Get total price (price + service_fee)
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->price + $this->service_fee;
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Reserve tickets (temporary hold during checkout)
     */
    public function reserve(int $quantity): bool
    {
        if ($this->available < $quantity) {
            return false;
        }

        $this->increment('reserved', $quantity);
        return true;
    }

    /**
     * Release reserved tickets (checkout expired/cancelled)
     */
    public function releaseReserved(int $quantity): void
    {
        $this->decrement('reserved', min($quantity, $this->reserved));
    }

    /**
     * Convert reserved to sold (payment completed)
     */
    public function confirmSale(int $quantity): void
    {
        $this->decrement('reserved', $quantity);
        $this->increment('sold', $quantity);
    }

    /**
     * Refund tickets (return to available pool)
     */
    public function refund(int $quantity): void
    {
        $this->decrement('sold', min($quantity, $this->sold));
    }
}
