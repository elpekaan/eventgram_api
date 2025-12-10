<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Models;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\Transfer\Enums\TransferStatus;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property float $asking_price
 * @property float $platform_commission
 * @property float $seller_receives
 * @property TransferStatus $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
        'expires_at',
        'venue_approved_at',
        'rejection_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatus::class, // Enum Casting
            'asking_price' => 'decimal:2',
            'platform_commission' => 'decimal:2',
            'seller_receives' => 'decimal:2',
            'expires_at' => 'datetime',
            'venue_approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
