<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Enums;

enum TransferStatus: string
{
    case PENDING_VENUE_APPROVAL = 'pending_venue_approval'; // Mekan onayı bekliyor
    case PENDING_BUYER_ACCEPTANCE = 'pending_buyer_acceptance'; // Alıcı onayı bekliyor
    case PENDING_PAYMENT = 'pending_payment'; // Ödeme bekliyor
    case COMPLETED = 'completed'; // Bitti (Para satıcıya aktarılacak)
    case REJECTED = 'rejected'; // Reddedildi
    case CANCELLED = 'cancelled'; // Satıcı iptal etti
    case FAILED = 'failed'; // Ödeme veya sistem hatası
}
