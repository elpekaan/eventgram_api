<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Events;

use App\Modules\CheckIn\Models\CheckIn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCheckedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CheckIn $checkIn,
    ) {}
}
