<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\DTOs;

use App\Modules\CheckIn\Models\CheckIn;
use App\Modules\Ticket\Models\Ticket;

final readonly class CheckInResponseDTO
{
    public function __construct(
        public CheckIn $checkIn,
        public Ticket $ticket,
        public bool $wasLate,
        public bool $locationVerified,
        public string $message,
    ) {}
}
