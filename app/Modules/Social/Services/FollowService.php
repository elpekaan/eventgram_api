<?php

declare(strict_types=1);

namespace App\Modules\Social\Services;

use App\Contracts\Services\FollowServiceInterface;
use App\Modules\Social\Events\VenueFollowed;
use App\Modules\Social\Events\VenueUnfollowed;
use App\Modules\User\Models\User;
use App\Modules\Venue\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FollowService implements FollowServiceInterface
{
    /**
     * Toggle follow/unfollow venue
     */
    public function toggleFollowVenue(User $user, int $venueId): array
    {
        return DB::transaction(function () use ($user, $venueId) {
            $venue = Venue::findOrFail($venueId);

            if ($user->isFollowing($venue)) {
                // Unfollow
                $user->unfollow($venue);
                $venue->decrement('total_followers');

                event(new VenueUnfollowed($user, $venue));

                Log::info('Venue unfollowed', [
                    'user_id' => $user->id,
                    'venue_id' => $venue->id,
                ]);

                return [
                    'action' => 'unfollowed',
                    'message' => 'Mekan takipten çıkarıldı.',
                ];
            }

            // Follow
            $user->follow($venue);
            $venue->increment('total_followers');

            event(new VenueFollowed($user, $venue));

            Log::info('Venue followed', [
                'user_id' => $user->id,
                'venue_id' => $venue->id,
            ]);

            return [
                'action' => 'followed',
                'message' => 'Mekan takip edildi.',
            ];
        });
    }

    /**
     * Get venue followers
     */
    public function getFollowers(int $venueId): array
    {
        $venue = Venue::findOrFail($venueId);

        return $venue->followers()
            ->select(['users.id', 'users.name', 'users.avatar_url'])
            ->get()
            ->toArray();
    }

    /**
     * Get user's followed venues
     */
    public function getFollowing(int $userId): array
    {
        $user = User::findOrFail($userId);

        return $user->followedVenues()
            ->select(['venues.id', 'venues.name', 'venues.slug', 'venues.logo_url', 'venues.city'])
            ->get()
            ->toArray();
    }
}
