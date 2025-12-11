<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\User\Models\User;
use App\Modules\Venue\DTOs\CreateVenueDTO;
use App\Modules\Venue\Models\Venue;

interface VenueServiceInterface
{
    public function create(User $user, CreateVenueDTO $dto): Venue;
    
    public function approve(int $venueId, int $adminId, string $notes): Venue;
    
    public function reject(int $venueId, int $adminId, string $reason): Venue;
    
    public function suspend(int $venueId, int $adminId, string $reason): Venue;
}
