<?php

declare(strict_types=1);

namespace App\Modules/Ticket\Enums;

enum TicketStatus: string
{
    case ACTIVE = 'active';
    case USED = 'used';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case TRANSFERRED = 'transferred';
    case CHARGEBACK = 'chargeback';
}
