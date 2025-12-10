<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Models;

use App\Modules\Event\Models\Event;
use App\Modules\Event\Models\EventTicketType;
use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property int $event_id
 * @property int $event_ticket_type_id
 * @property string $code
 * @property TicketStatus $status
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_transferred
 * @property bool $is_locked
 * @property string|null $locked_reason
 * @property \Illuminate\Support\Carbon|null $transferred_at
 * @property int|null $transferred_from
 * @property \Illuminate\Support\Carbon|null $qr_regenerated_at
 * @property-read \App\Modules\Event\Models\EventTicketType $ticketType
 */
class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'event_id',
        'event_ticket_type_id',
        'code',
        'status',
        'used_at',
        'is_transferred',
        'is_locked',
        'locked_reason',
        'transferred_at',
        'transferred_from',
        'qr_regenerated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class, // Enum Casting
            'used_at' => 'datetime',
            'is_transferred' => 'boolean',
            'is_locked' => 'boolean',
            'transferred_at' => 'datetime',
            'qr_regenerated_at' => 'datetime',
        ];
    }

    // İlişkiler
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicketType::class, 'event_ticket_type_id');
    }
}
