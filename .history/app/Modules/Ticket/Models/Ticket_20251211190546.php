<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Models;

use App\Modules\Event\Models\Event;
use App\Modules\Event\Models\EventTicketType;
use App\Modules\Order\Models\Order;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $event_id
 * @property int $ticket_type_id
 * @property string $ticket_code
 * @property string|null $qr_code_url
 * @property bool $is_transferred
 * @property bool $transfer_completed
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property int|null $checked_in_by
 * @property string|null $check_in_device_id
 * @property float|null $check_in_latitude
 * @property float|null $check_in_longitude
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $chargeback_at
 * @property bool $is_locked
 * @property string|null $locked_reason
 * @property \Illuminate\Support\Carbon|null $transferred_at
 * @property int|null $transferred_from
 * @property \Illuminate\Support\Carbon|null $qr_regenerated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $owner
 * @property-read Order $order
 * @property-read Event $event
 * @property-read EventTicketType $ticketType
 * @property-read User|null $checkedInBy
 * @property-read User|null $transferredFrom
 * @property-read bool $is_active
 * @property-read bool $is_used
 * @property-read bool $is_refunded
 * @property-read bool $can_be_transferred
 * @property-read bool $can_be_checked_in
 */
class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'event_id',
        'ticket_type_id',
        'ticket_code',
        'qr_code_url',
        'is_transferred',
        'transfer_completed',
        'status',
        'used_at',
        'checked_in_by',
        'check_in_device_id',
        'check_in_latitude',
        'check_in_longitude',
        'refunded_at',
        'chargeback_at',
        'is_locked',
        'locked_reason',
        'transferred_at',
        'transferred_from',
        'qr_regenerated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_transferred' => 'boolean',
            'transfer_completed' => 'boolean',
            'is_locked' => 'boolean',
            'used_at' => 'datetime',
            'refunded_at' => 'datetime',
            'chargeback_at' => 'datetime',
            'transferred_at' => 'datetime',
            'qr_regenerated_at' => 'datetime',
            'check_in_latitude' => 'decimal:7',
            'check_in_longitude' => 'decimal:7',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Ticket owner (current holder)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Original order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Ticket's event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Ticket type (VIP, Normal, etc.)
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicketType::class, 'ticket_type_id');
    }

    /**
     * Staff who checked in this ticket
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Previous owner (if transferred)
     */
    public function transferredFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_from');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Only active tickets
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Only used tickets
     */
    public function scopeUsed(Builder $query): Builder
    {
        return $query->where('status', 'used');
    }

    /**
     * Scope: Only refunded tickets
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope: Tickets for a specific event
     */
    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope: Tickets owned by a user
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Not locked tickets
     */
    public function scopeNotLocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Check if ticket is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if ticket is used
     */
    public function getIsUsedAttribute(): bool
    {
        return $this->status === 'used';
    }

    /**
     * Check if ticket is refunded
     */
    public function getIsRefundedAttribute(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if ticket can be transferred
     */
    public function getCanBeTransferredAttribute(): bool
    {
        // Cannot transfer if:
        // - Already used
        // - Refunded
        // - Currently locked
        // - Already in transfer process
        return $this->is_active
            && !$this->is_locked
            && !$this->is_transferred;
    }

    /**
     * Check if ticket can be checked in
     */
    public function getCanBeCheckedInAttribute(): bool
    {
        // Can only check in active tickets
        return $this->is_active && !$this->is_used;
    }

    // ========================================
    // METHODS
    // ========================================

    /**
     * Mark ticket as used (checked in)
     */
    public function markAsUsed(int $staffId, ?array $location = null): void
    {
        $data = [
            'status' => 'used',
            'used_at' => now(),
            'checked_in_by' => $staffId,
        ];

        if ($location) {
            $data['check_in_latitude'] = $location['latitude'] ?? null;
            $data['check_in_longitude'] = $location['longitude'] ?? null;
            $data['check_in_device_id'] = $location['device_id'] ?? null;
        }

        $this->update($data);
    }

    /**
     * Lock ticket (during transfer process)
     */
    public function lock(string $reason): void
    {
        $this->update([
            'is_locked' => true,
            'locked_reason' => $reason,
        ]);
    }

    /**
     * Unlock ticket
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_reason' => null,
        ]);
    }

    /**
     * Mark ticket as refunded
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }

    /**
     * Regenerate QR code (after transfer)
     */
    public function regenerateQrCode(): void
    {
        // Generate new ticket code
        $newCode = self::generateTicketCode();

        $this->update([
            'ticket_code' => $newCode,
            'qr_regenerated_at' => now(),
        ]);

        // TODO: Generate new QR code image and update qr_code_url
    }

    /**
     * Generate unique ticket code
     */
    public static function generateTicketCode(): string
    {
        return strtoupper(substr(uniqid() . bin2hex(random_bytes(4)), 0, 12));
    }
}
