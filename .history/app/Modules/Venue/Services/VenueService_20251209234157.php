<?php

declare(strict_types=1);

namespace App\Modules\Venue\Services;

use App\Modules\User\Models\User;
use App\Modules\Venue\DTOs\CreateVenueDTO;
use App\Modules\Venue\Enums\VenueStatus;
use App\Modules\Venue\Models\Venue;
use Illuminate\Support\Str;

class VenueService
{
    public function create(User $user, CreateVenueDTO $dto): Venue
    {
        // 1. Slug oluştur (Otomatik)
        $slug = Str::slug($dto->name);

        // 2. Mekanı kaydet
        // create metodu, Model'deki $fillable alanlarını kullanır.
        $venue = Venue::create([
            'user_id' => $user->id,
            'name' => $dto->name,
            'slug' => $slug,
            'description' => $dto->description,
            'city' => $dto->city,
            'address' => $dto->address,
            'capacity' => $dto->capacity,
            'phone' => $dto->phone,
            'website' => $dto->website,
            'status' => VenueStatus::PENDING, // Varsayılan olarak onay bekliyor
        ]);

        return $venue;
    }
}
