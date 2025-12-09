<?php

declare(strict_types=1);

namespace App\Modules\Venue\DTOs;

use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateVenueDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $description,
        public string $city,
        public string $address,
        public int $capacity,
        public ?string $phone = null,
        public ?string $website = null,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        return new static(
            name: $data['name'],
            description: $data['description'] ?? '', // Null ise boş string
            city: $data['city'],
            address: $data['address'],
            capacity: (int) $data['capacity'],
            phone: $data['phone'] ?? null,
            website: $data['website'] ?? null,
        );
    }
}
