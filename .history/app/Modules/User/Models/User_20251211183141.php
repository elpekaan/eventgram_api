<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Social\Traits\CanFollow;

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
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'role',  // 'user', 'venue', 'admin'
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
            'permissions' => 'array',  // JSON
            'birth_date' => 'date',
            'points' => 'integer',
            'level' => 'integer',
        ];
    }
    /**
     * User'ın venue'su (eğer venue owner ise)
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * User'ın oluşturduğu siparişler
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * User'ın sahip olduğu biletler
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * User'ın yaptığı check-in'ler
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * User'ın transfer ettiği biletler (seller)
     */
    public function sentTransfers(): HasMany
    {
        return $this->hasMany(TicketTransfer::class, 'seller_id');
    }

    /**
     * User'ın aldığı biletler (buyer)
     */
    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(TicketTransfer::class, 'buyer_id');
    }
}
