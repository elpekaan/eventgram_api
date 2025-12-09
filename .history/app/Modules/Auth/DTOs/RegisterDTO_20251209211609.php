<?php

namespace App\Modules\Auth\DTOs;

readonly class RegisterDTO extends BaseDTO {
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $device_name = null,
    ) {}

    public static function fromRequest(Request $request): static {

        $data = $request->validated();
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            device_name: $request->header
        )
    }
}
