<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Carbon\Carbon;

interface PaymentReconciliationServiceInterface
{
    public function reconcileDate(Carbon $date): array;
}
