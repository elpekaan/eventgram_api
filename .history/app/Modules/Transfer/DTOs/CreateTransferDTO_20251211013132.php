<?php

declare(strict_types=1);

namespace App\Modules\Transfer\DTOs;

use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateTransferDTO extends BaseDTO
{
    public function __construct(
        public int $ticketId,
        public string $buyerEmail,
        public float $askingPrice,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        return new static(
            ticketId: (int) $data['ticket_id'],
            buyerEmail: (string) $data['buyer_email'],
            askingPrice: (float) $data['asking_price'],
        );
    }
}
