<?php

declare(strict_types=1);

namespace App\Modules\Event\Services;

use App\Modules\Event\DTOs\CreateEventDTO;
use App\Modules\Event\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    public function create(CreateEventDTO $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            // 1. Etkinliği Oluştur
            $event = Event::create([
                'venue_id' => $dto->venueId,
                'name' => $dto->name,
                'slug' => Str::slug($dto->name) . '-' . Str::random(6), // Unique slug
                'description' => $dto->description,
                'start_time' => $dto->start_time,
                'end_time' => $dto->end_time,
                'category' => $dto->category,
                'status' => 'draft', // Varsayılan olarak taslak başlar
            ]);

            // 2. Bilet Tiplerini Oluştur (Bulk Insert yerine loop tercih ediyorum, model eventleri tetiklensin diye)
            foreach ($dto->ticketTypes as $ticketTypeDto) {
                $event->ticketTypes()->create($ticketTypeDto->toArray());
            }

            return $event->load('ticketTypes'); // İlişkisiyle beraber dön
        });
    }
}
