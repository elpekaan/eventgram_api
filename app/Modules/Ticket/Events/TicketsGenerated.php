<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Events;

use App\Modules\Order\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketsGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public int $ticketCount,
    ) {}
}
