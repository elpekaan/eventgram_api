<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use Illuminate\Http\Request;

readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $device_name = null,
    ) {}

    public static function fromRequest(Request $request): static {
        $data = $request->validate();

        return new self(
            email: $data['email'],
            
        )
    }
}
