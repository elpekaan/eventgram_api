<?php

declare(strict_types=1);

namespace App\Modules\Event\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';         // Taslak (Sadece mekan sahibi görür)
    case PUBLISHED = 'published'; // Yayında (Bilet alınabilir)
    case CANCELLED = 'cancelled'; // İptal edildi
    case COMPLETED = 'completed'; // Etkinlik bitti
}
