<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case REFUND_REQUESTED = 'refund_requested';
    case CHARGEBACK = 'chargeback';
}
