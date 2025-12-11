<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\CheckIn\DTOs\CheckInRequestDTO;
use App\Modules\CheckIn\DTOs\CheckInResponseDTO;

interface CheckInServiceInterface
{
    public function checkIn(CheckInRequestDTO $dto): CheckInResponseDTO;
    
    public function getEventCheckIns(int $eventId): array;
    
    public function getCheckInStats(int $eventId): array;
}
