<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Transfer\DTOs\CreateTransferDTO;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\User\Models\User;

interface TicketTransferServiceInterface
{
    public function createTransfer(User $seller, CreateTransferDTO $dto): TicketTransfer;
    
    public function approveByVenue(User $user, TicketTransfer $transfer): TicketTransfer;
    
    public function rejectByVenue(User $user, TicketTransfer $transfer, string $reason): TicketTransfer;
    
    public function acceptByBuyer(User $user, TicketTransfer $transfer): TicketTransfer;
    
    public function completeTransfer(TicketTransfer $transfer, PaymentTransaction $transaction): void;
    
    public function cancelTransfer(TicketTransfer $transfer, string $reason): void;
}
