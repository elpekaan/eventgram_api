<?php

declare(strict_types=1);

namespace App\Modules\Payment\Events;

use App\Modules\Payment\Models\Chargeback;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChargebackReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Chargeback $chargeback,
    ) {}
}
