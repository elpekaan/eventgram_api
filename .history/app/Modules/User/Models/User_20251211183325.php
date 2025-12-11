<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Social\Traits\CanFollow;
use App\Modules\Venue\Models\Venue;
use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Models\Ticket;
use App\Modules\Transfer\Models\TicketTransfer;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_admin
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanFollow;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'birth_date',
        'gender',
        'avatar_url',
        'bio',
        'city',
        'country',
        'role',
        'venue_id',
        'permissions',
        'two_factor_secret',
        'two_factor_enabled',
        'points',
        'level',
        'email_verification_token',
        'phone_verification_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'email_verification_token',
        'phone_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'permissions' => 'array',
            'birth_date' => 'date',
            'points' => 'integer',
            'level' => 'integer',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * User's venue (if venue owner)
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * User's orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * User's tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * User's sent transfers (as seller)
     */
    public function sentTransfers(): HasMany
    {
        return $this->hasMany(TicketTransfer::class, 'seller_id');
    }

    /**
     * User's received transfers (as buyer)
     */
    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(TicketTransfer::class, 'buyer_id');
    }
}
