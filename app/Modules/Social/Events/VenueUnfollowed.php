<?php

declare(strict_types=1);

namespace App\Modules\Social\Events;

use App\Modules\User\Models\User;
use App\Modules\Venue\Models\Venue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VenueUnfollowed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Venue $venue,
    ) {}
}
