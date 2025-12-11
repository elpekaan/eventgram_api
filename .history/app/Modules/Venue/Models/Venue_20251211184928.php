<?php

declare(strict_types=1);

namespace App\Modules\Venue\Models;

use App\Modules\User\Models\User;
use App\Modules\Event\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Searchable;

class Venue extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'category_id',
        'description',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'district',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'tax_office',
        'tax_number',
        'trade_registry_number',
        'logo_url',
        'cover_url',
        'gallery',
        'capacity',
        'status',
        'verified_at',
        'verified_by',
        'approval_notes',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'commission_rate',
        'instagram',
        'twitter',
        'facebook',
        'total_events',
        'total_followers',
    ];

    protected $hidden = [
        'tax_office',
        'tax_number',
        'trade_registry_number',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'capacity' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'commission_rate' => 'decimal:4',
            'total_events' => 'integer',
            'total_followers' => 'integer',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Venue owner
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Admin who verified the venue
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Admin who rejected the venue
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Admin who suspended the venue
     */
    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    /**
     * Venue's events
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Followers (polymorphic)
     */
    public function follows(): MorphMany
    {
        return $this->morphMany(
            'App\Modules\Social\Models\Follow',
            'followable'
        );
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Only verified venues
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope: Only pending venues
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Search by name or description
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: Filter by city
     */
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Check if venue is verified
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if venue is pending
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if venue is suspended
     */
    public function getIsSuspendedAttribute(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->district,
            $this->city,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    // ========================================
    // SCOUT SEARCHABLE
    // ========================================

    /**
     * Get the indexable data array for Scout
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'city' => $this->city,
            'status' => $this->status,
            'capacity' => $this->capacity,
            'total_events' => $this->total_events,
            'total_followers' => $this->total_followers,
        ];
    }

    /**
     * Determine if the model should be searchable
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'verified' && !$this->trashed();
    }
}
