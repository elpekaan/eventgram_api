<?php

declare(strict_types=1);

namespace App\Modules\Event\Enums;

enum EventCategory: string
{
    case CONCERT = 'concert';
    case THEATER = 'theater';
    case WORKSHOP = 'workshop';
    case CONFERENCE = 'conference';
    case NIGHT_LIFE = 'night_life';
}
