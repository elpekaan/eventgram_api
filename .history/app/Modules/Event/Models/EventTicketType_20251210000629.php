<?php

declare(strict_types=1);

namespace App\Modules\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property float $price
 * @property int $capacity
 * @property int $sold_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class EventTicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'capacity',
        'sold_count'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', // PHP tarafında string gelebilir, dikkat. Float/String dönüşümü.
            'capacity' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
