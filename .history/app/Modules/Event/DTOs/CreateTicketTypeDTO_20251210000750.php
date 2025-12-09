<?php

declare(strict_types=1);

namespace App\Modules\Event\DTOs;

final readonly class CreateTicketTypeDTO
{
    public function __construct(
        public string $name,
        public float $price,
        public int $capacity,
    ) {}

    // Bu DTO doğrudan Request'ten değil, bir array'den oluşacak.
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            price: (float) $data['price'],
            capacity: (int) $data['capacity'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'capacity' => $this->capacity,
        ];
    }
}
