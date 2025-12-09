<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $device_name = null,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        return new static(
            email: $data['email'],
            password: $data['password'],
            device_name: $request->header('User-Agent'),
        );
    }
}
