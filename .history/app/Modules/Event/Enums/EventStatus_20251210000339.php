<?php

declare(strict_types=1);

namespace App\Modules\Event\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';         // Taslak
    case PUBLISHED = 'published'; // Yayında (Satışa açık)
    case CANCELLED = 'cancelled'; // İptal
    case COMPLETED = 'completed'; // Bitti
}
