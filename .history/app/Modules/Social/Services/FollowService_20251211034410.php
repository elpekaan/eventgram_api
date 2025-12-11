<?php

declare(strict_types=1);

namespace App\Modules\Social\Services;

use App\Modules\User\Models\User;
use App\Modules\Venue\Models\Venue;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FollowService
{
    /**
     * @return array{action: string, message: string}
     */
    public function toggleFollowVenue(User $user, int $venueId): array
    {
        $venue = Venue::findOrFail($venueId);

        if ($user->isFollowing($venue)) {
            $user->unfollow($venue);
            return ['action' => 'unfollowed', 'message' => 'Mekan takipten çıkarıldı.'];
        }

        $user->follow($venue);
        return ['action' => 'followed', 'message' => 'Mekan takip edildi.'];
    }
}
