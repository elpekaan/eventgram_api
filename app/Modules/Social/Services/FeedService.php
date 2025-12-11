<?php

declare(strict_types=1);

namespace App\Modules\Social\Services;

use App\Modules\Event\Enums\EventStatus;
use App\Modules\Event\Models\Event;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class FeedService
{
    /**
     * @return Collection<int, Event>
     */
    public function getUserFeed(User $user): Collection
    {
        $cacheKey = "user_feed:{$user->id}";

        // 10 dakika boyunca Redis'ten oku, yoksa DB'den çekip yaz.
        return Cache::remember($cacheKey, 600, function () use ($user) {

            // 1. Takip edilen mekanların ID'lerini al
            $venueIds = $user->followedVenues()->pluck('venues.id')->toArray();

            if (empty($venueIds)) {
                return new Collection(); // Boş koleksiyon
            }

            // 2. Bu mekanların "Yayında" olan etkinliklerini getir
            return Event::whereIn('venue_id', $venueIds)
                ->where('status', EventStatus::PUBLISHED)
                ->where('start_time', '>', now()) // Geçmiş etkinlikleri gösterme
                ->orderBy('start_time', 'asc')
                ->with('venue') // N+1 sorunu olmasın
                ->limit(50)
                ->get();
        });
    }
}
