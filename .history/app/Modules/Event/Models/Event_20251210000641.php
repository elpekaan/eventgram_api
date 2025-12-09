<?php

declare(strict_types=1);

namespace App\Modules\Event\Models;

use App\Modules\Event\Enums\EventCategory;
use App\Modules\Event\Enums\EventStatus;
use App\Modules\Venue\Models\Venue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $venue_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string|null $poster_image
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property EventCategory $category
 * @property EventStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Venue $venue
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventTicketType> $ticketTypes
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venue_id',
        'name',
        'slug',
        'description',
        'poster_image',
        'start_time',
        'end_time',
        'category',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'category' => EventCategory::class,
            'status' => EventStatus::class,
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicketType::class);
    }
}
