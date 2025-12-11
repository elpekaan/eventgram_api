<?php

declare(strict_types=1);

namespace App\Modules\Venue\Services;

use App\Contracts\Services\VenueServiceInterface;
use App\Modules\User\Models\User;
use App\Modules\Venue\DTOs\CreateVenueDTO;
use App\Modules\Venue\Events\VenueCreated;
use App\Modules\Venue\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VenueService implements VenueServiceInterface
{
    public function create(User $user, CreateVenueDTO $dto): Venue
    {
        return DB::transaction(function () use ($user, $dto) {
            $venue = Venue::create([
                'user_id' => $user->id,
                'name' => $dto->name,
                'slug' => $this->generateUniqueSlug($dto->name),
                'description' => $dto->description,
                'city' => $dto->city,
                'address' => $dto->address,
                'capacity' => $dto->capacity,
                'phone' => $dto->phone,
                'website' => $dto->website,
                'status' => 'pending',
                'total_events' => 0,
                'total_followers' => 0,
            ]);

            event(new VenueCreated($venue));

            Log::info('Venue created', ['venue_id' => $venue->id, 'user_id' => $user->id]);

            return $venue;
        });
    }

    public function approve(int $venueId, int $adminId, string $notes): Venue
    {
        return DB::transaction(function () use ($venueId, $adminId, $notes) {
            $venue = Venue::findOrFail($venueId);
            
            $venue->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $adminId,
                'approval_notes' => $notes,
            ]);

            Log::info('Venue approved', ['venue_id' => $venue->id, 'admin_id' => $adminId]);

            return $venue;
        });
    }

    public function reject(int $venueId, int $adminId, string $reason): Venue
    {
        return DB::transaction(function () use ($venueId, $adminId, $reason) {
            $venue = Venue::findOrFail($venueId);
            
            $venue->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $adminId,
                'rejection_reason' => $reason,
            ]);

            Log::info('Venue rejected', ['venue_id' => $venue->id, 'admin_id' => $adminId]);

            return $venue;
        });
    }

    public function suspend(int $venueId, int $adminId, string $reason): Venue
    {
        return DB::transaction(function () use ($venueId, $adminId, $reason) {
            $venue = Venue::findOrFail($venueId);
            
            $venue->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspended_by' => $adminId,
                'suspension_reason' => $reason,
            ]);

            Log::info('Venue suspended', ['venue_id' => $venue->id, 'admin_id' => $adminId]);

            return $venue;
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = 1;

        while (Venue::where('slug', $slug)->exists()) {
            $slug = Str::slug($name) . '-' . $count++;
        }

        return $slug;
    }
}
