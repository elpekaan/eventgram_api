<?php

declare(strict_types=1);

namespace App\Modules\Event\Events;

use App\Modules\Event\Models\Event;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Event $event,
    ) {}
}
