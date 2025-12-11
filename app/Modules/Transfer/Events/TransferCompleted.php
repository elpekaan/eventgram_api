<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Events;

use App\Modules\Transfer\Models\TicketTransfer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TicketTransfer $transfer,
    ) {}
}
