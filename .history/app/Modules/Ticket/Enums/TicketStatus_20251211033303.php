<?php

declare(strict_types=1);

namespace App\Modules\Ticket\Enums;

enum TicketStatus: string
{
    case ACTIVE = 'active';       // Kullanılabilir
    case USED = 'used';           // Kapıda okutuldu
    case CANCELLED = 'cancelled'; // İptal edildi
    case BLOCKED = 'blocked';
}
