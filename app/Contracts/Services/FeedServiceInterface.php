<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface FeedServiceInterface
{
    public function getUserFeed(User $user): Collection;
    
    public function clearUserFeedCache(int $userId): void;
}
