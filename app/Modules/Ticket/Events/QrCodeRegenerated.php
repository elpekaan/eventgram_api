<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Events;

use App\Modules\Ticket\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QrCodeRegenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $oldCode,
    ) {}
}
