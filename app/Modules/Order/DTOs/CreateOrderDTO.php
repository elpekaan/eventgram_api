<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

use App\DTOs\BaseDTO;
use Illuminate\Http\Request;

final readonly class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $userId,
        public int $eventId,
        public int $ticketTypeId,
        public int $quantity,
        public ?string $couponCode,
        public string $ipAddress,
        public string $userAgent,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: $request->user()->id,
            eventId: (int) $request->input('event_id'),
            ticketTypeId: (int) $request->input('ticket_type_id'),
            quantity: (int) $request->input('quantity'),
            couponCode: $request->input('coupon_code'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }
}
