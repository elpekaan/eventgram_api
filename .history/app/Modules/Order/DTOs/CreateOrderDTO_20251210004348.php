<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $userId,
        public int $eventId,
        public int $ticketTypeId,
        public int $quantity,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        // User ID'yi request body'den değil, Auth token'dan alıyoruz (Güvenlik)
        /** @var \App\Modules\User\Models\User $user */
        $user = $request->user();
        $data = $request->validated();

        return new static(
            userId: $user->id,
            eventId: (int) $data['event_id'],
            ticketTypeId: (int) $data['ticket_type_id'],
            quantity: (int) $data['quantity'],
        );
    }
}
