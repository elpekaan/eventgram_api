<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Modules\Event\Models\Event;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property int $event_ticket_type_id
 * @property int $quantity
 * @property string $reference_code
 * @property float $total_amount
 * @property OrderStatus $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read Event $event
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ticket> $tickets
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'event_ticket_type_id',
        'quantity',
        'reference_code',
        'total_amount',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class, // Enum Casting
            'total_amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    // İlişkiler
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
