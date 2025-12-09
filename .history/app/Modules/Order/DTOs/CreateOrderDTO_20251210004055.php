<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public int $ticketTypeId,
        public int $quantity,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        return new static(
            eventId: (int) $data['event_id'],
            ticketTypeId: (int) $data['ticket_type_id'],
            quantity: (int) $data['quantity'],
        );
    }
}
