<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\User\Models\User;

interface FollowServiceInterface
{
    public function toggleFollowVenue(User $user, int $venueId): array;
    
    public function getFollowers(int $venueId): array;
    
    public function getFollowing(int $userId): array;
}
