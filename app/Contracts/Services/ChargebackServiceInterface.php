<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Payment\Models\Chargeback;

interface ChargebackServiceInterface
{
    public function handleDispute(PaymentTransaction $transaction, array $bankData): Chargeback;
    
    public function submitEvidence(int $chargebackId, array $evidence, string $notes): Chargeback;
    
    public function markAsWon(int $chargebackId, string $notes): Chargeback;
    
    public function markAsLost(int $chargebackId, string $notes): Chargeback;
}
