<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';     // Ödeme bekleniyor (Locking devrede)
    case PAID = 'paid';           // Ödeme başarılı
    case FAILED = 'failed';       // Ödeme başarısız veya zaman aşımı
    case REFUNDED = 'refunded';
    case CHARGEBACK = 'chargeback';  // İade edildi
}
