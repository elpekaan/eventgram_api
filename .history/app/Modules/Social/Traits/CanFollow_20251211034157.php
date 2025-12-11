<?php

declare(strict_types=1);

namespace App\Modules\Social\Traits;

use App\Modules\User\Models\User;
use App\Modules\Venue\Models\Venue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait CanFollow
{
    // Takip ettiklerim (Venue)
    public function followedVenues(): MorphToMany
    {
        return $this->morphedByMany(
            Venue::class,
            'followable',
            'follows',
            'follower_id',
            'followable_id'
        );
    }

    public function follow(Model $model): void
    {
        if ($model instanceof Venue) {
            $this->followedVenues()->syncWithoutDetaching([$model->id]);
        }
    }

    public function unfollow(Model $model): void
    {
        if ($model instanceof Venue) {
            $this->followedVenues()->detach($model->id);
        }
    }

    public function isFollowing(Model $model): bool
    {
        if ($model instanceof Venue) {
            return $this->followedVenues()->where('followable_id', $model->id)->exists();
        }
        return false;
    }
}
