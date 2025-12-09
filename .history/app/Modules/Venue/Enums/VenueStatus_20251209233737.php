<?php

declare(strict_types=1);

namespace App\Modules\Venue\Enums;

enum VenueStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';
}
