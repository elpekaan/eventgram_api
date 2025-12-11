<?php

declare(strict_types=1);

namespace App\Modules\Event\Models;

use App\Modules\User\Models\User;
use App\Modules\Venue\Models\Venue;
use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $venue_id
 * @property int $created_by
 * @property string $name
 * @property string $slug
 * @property int|null $category_id
 * @property string $description
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $doors_open
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property string $timezone
 * @property string|null $location_address
 * @property float|null $location_latitude
 * @property float|null $location_longitude
 * @property string|null $poster_url
 * @property string|null $banner_url
 * @property array|null $gallery
 * @property \Illuminate\Support\Carbon|null $sales_start
 * @property \Illuminate\Support\Carbon|null $sales_end
 * @property int $max_tickets_per_order
 * @property int $check_in_opens_hours
 * @property int $late_entry_hours
 * @property bool $allow_late_entry
 * @property string $check_in_status
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property \Illuminate\Support\Carbon|null $blocked_at
 * @property int|null $blocked_by
 * @property string|null $block_reason
 * @property int $total_capacity
 * @property int $tickets_sold
 * @property int $checked_in_count
 * @property int $views_count
 * @property int $likes_count
 * @property int $shares_count
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array|null $meta_keywords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Venue $venue
 * @property-read User $creator
 * @property-read User|null $blockedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<EventTicketType> $ticketTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<Ticket> $tickets
 * @property-read bool $is_published
 * @property-read bool $is_upcoming
 * @property-read bool $is_past
 * @property-read bool $is_cancelled
 * @property-read bool $is_blocked
 * @property-read bool $is_sold_out
 * @property-read int $available_tickets
 * @property-read float $attendance_rate
 */
class Event extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'venue_id',
        'created_by',
        'name',
        'slug',
        'category_id',
        'description',
        'date',
        'doors_open',
        'ends_at',
        'timezone',
        'location_address',
        'location_latitude',
        'location_longitude',
        'poster_url',
        'banner_url',
        'gallery',
        'sales_start',
        'sales_end',
        'max_tickets_per_order',
        'check_in_opens_hours',
        'late_entry_hours',
        'allow_late_entry',
        'check_in_status',
        'status',
        'published_at',
        'cancelled_at',
        'cancellation_reason',
        'blocked_at',
        'blocked_by',
        'block_reason',
        'total_capacity',
        'tickets_sold',
        'checked_in_count',
        'views_count',
        'likes_count',
        'shares_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'doors_open' => 'datetime',
            'ends_at' => 'datetime',
            'sales_start' => 'datetime',
            'sales_end' => 'datetime',
            'published_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'blocked_at' => 'datetime',
            'gallery' => 'array',
            'meta_keywords' => 'array',
            'location_latitude' => 'decimal:7',
            'location_longitude' => 'decimal:7',
            'max_tickets_per_order' => 'integer',
            'check_in_opens_hours' => 'integer',
            'late_entry_hours' => 'integer',
            'allow_late_entry' => 'boolean',
            'total_capacity' => 'integer',
            'tickets_sold' => 'integer',
            'checked_in_count' => 'integer',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'shares_count' => 'integer',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Event's venue
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Event creator (venue owner/admin)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Admin who blocked the event
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Event's ticket types
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicketType::class);
    }

    /**
     * Event's orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Event's tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Only published events
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at');
    }

    /**
     * Scope: Only upcoming events
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>', now())
            ->where('status', 'published');
    }

    /**
     * Scope: Only past events
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('date', '<', now())
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope: Events in a specific city
     */
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->whereHas('venue', function ($q) use ($city) {
            $q->where('city', $city);
        });
    }

    /**
     * Scope: Search by name or description
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Check if event is published
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    /**
     * Check if event is upcoming
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->date > now() && $this->status === 'published';
    }

    /**
     * Check if event is past
     */
    public function getIsPastAttribute(): bool
    {
        return $this->date < now();
    }

    /**
     * Check if event is cancelled
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if event is blocked
     */
    public function getIsBlockedAttribute(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Check if event is sold out
     */
    public function getIsSoldOutAttribute(): bool
    {
        return $this->tickets_sold >= $this->total_capacity;
    }

    /**
     * Get available tickets count
     */
    public function getAvailableTicketsAttribute(): int
    {
        return max(0, $this->total_capacity - $this->tickets_sold);
    }

    /**
     * Get attendance rate (percentage)
     */
    public function getAttendanceRateAttribute(): float
    {
        if ($this->tickets_sold === 0) {
            return 0.0;
        }

        return round(($this->checked_in_count / $this->tickets_sold) * 100, 2);
    }

    /**
     * Get check-in window opens at
     */
    public function getCheckInOpensAtAttribute(): \Illuminate\Support\Carbon
    {
        return $this->date->copy()->subHours($this->check_in_opens_hours);
    }

    /**
     * Get check-in window closes at
     */
    public function getCheckInClosesAtAttribute(): \Illuminate\Support\Carbon
    {
        return $this->date->copy()->addHours($this->late_entry_hours);
    }

    /**
     * Check if check-in is currently open
     */
    public function getIsCheckInOpenAttribute(): bool
    {
        if ($this->check_in_status !== 'open') {
            return false;
        }

        $now = now();
        return $now >= $this->check_in_opens_at && $now <= $this->check_in_closes_at;
    }

    // ========================================
    // SCOUT SEARCHABLE
    // ========================================

    /**
     * Get the indexable data array for Scout
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing('venue');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'date' => $this->date->timestamp,
            'venue_id' => $this->venue_id,
            'venue_name' => $this->venue->name,
            'city' => $this->venue->city,
            'tickets_sold' => $this->tickets_sold,
            'total_capacity' => $this->total_capacity,
            'views_count' => $this->views_count,
        ];
    }

    /**
     * Determine if the model should be searchable
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published' && !$this->trashed();
    }
}
