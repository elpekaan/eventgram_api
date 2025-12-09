<?php

declare(strict_types=1);

namespace App\Modules\Event\DTOs;

use App\Modules\Event\Enums\EventCategory;
use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateEventDTO extends BaseDTO
{
    /**
     * @param CreateTicketTypeDTO[] $ticketTypes
     */
    public function __construct(
        public int $venueId,
        public string $name,
        public string $description,
        public string $start_time,
        public ?string $end_time,
        public EventCategory $category,
        public ?string $poster_image,
        public array $ticketTypes, // Nested DTO Array
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        // Ticket array'ini DTO objelerine çeviriyoruz
        $ticketTypes = array_map(
            fn(array $ticket) => CreateTicketTypeDTO::fromArray($ticket),
            $data['tickets']
        );

        return new static(
            venueId: (int) $data['venue_id'],
            name: $data['name'],
            description: $data['description'],
            start_time: $data['start_time'],
            end_time: $data['end_time'] ?? null,
            category: EventCategory::from($data['category']), // String -> Enum
            poster_image: null, // Görsel upload sonraki iş
            ticketTypes: $ticketTypes
        );
    }
}
