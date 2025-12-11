<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Ticket\Models\Ticket;

interface TicketServiceInterface
{
    public function generateTickets(Order $order): void;
    
    public function regenerateQrCode(Ticket $ticket): Ticket;
    
    public function validateTicket(string $ticketCode, int $eventId): Ticket;
    
    public function getUserTickets(int $userId): array;
}
