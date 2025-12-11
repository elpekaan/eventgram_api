<?php

declare(strict_types=1);

namespace App\Modules\Venue\Events;

use App\Modules\Venue\Models\Venue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VenueCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Venue $venue) {}
}
