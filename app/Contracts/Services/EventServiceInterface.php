<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Event\DTOs\CreateEventDTO;
use App\Modules\Event\DTOs\UpdateEventDTO;
use App\Modules\Event\Models\Event;

interface EventServiceInterface
{
    public function create(CreateEventDTO $dto): Event;
    
    public function update(int $eventId, UpdateEventDTO $dto): Event;
    
    public function publish(int $eventId): Event;
    
    public function cancel(int $eventId, string $reason): Event;
    
    public function delete(int $eventId): void;
    
    public function getUpcoming(int $venueId): array;
}
