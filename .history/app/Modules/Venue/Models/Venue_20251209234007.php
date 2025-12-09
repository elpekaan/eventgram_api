<?php

declare(strict_types=1);

namespace App\Modules\Venue\Models;

use App\Modules\User\Models\User;
use App\Modules\Venue\Enums\VenueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $city
 * @property string $address
 * @property int $capacity
 * @property string|null $phone
 * @property string|null $website
 * @property VenueStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User $owner
 */
class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'city',
        'address',
        'capacity',
        'phone',
        'website',
        'status',
    ];

    // Enum Casting (Laravel 9+ özelliği)
    // DB'den gelen string 'pending' değerini otomatik VenueStatus enum'ına çevirir.
    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'capacity' => 'integer',
        ];
    }

    // İlişkiler: Mekanın bir sahibi vardır
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
