<?php

declare(strict_types=1);

namespace App\Modules\Social\Services;

use App\Contracts\Services\FeedServiceInterface;
use App\Modules\Event\Models\Event;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeedService implements FeedServiceInterface
{
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Get user's personalized feed
     */
    public function getUserFeed(User $user): Collection
    {
        $cacheKey = "user_feed:{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Get followed venues
            $venueIds = $user->followedVenues()->pluck('venues.id')->toArray();

            if (empty($venueIds)) {
                Log::info('User has no followed venues', ['user_id' => $user->id]);
                return new Collection();
            }

            // Get upcoming events from followed venues
            $events = Event::whereIn('venue_id', $venueIds)
                ->where('status', 'published')
                ->where('date', '>', now())
                ->orderBy('date', 'asc')
                ->with(['venue', 'ticketTypes'])
                ->limit(50)
                ->get();

            Log::info('Feed generated', [
                'user_id' => $user->id,
                'events_count' => $events->count(),
            ]);

            return $events;
        });
    }

    /**
     * Clear user's feed cache
     */
    public function clearUserFeedCache(int $userId): void
    {
        $cacheKey = "user_feed:{$userId}";
        Cache::forget($cacheKey);

        Log::info('Feed cache cleared', ['user_id' => $userId]);
    }
}
