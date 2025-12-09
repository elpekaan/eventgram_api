<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Shared\DTOs\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

// final ekledik. Artık 'new static()' güvenli.
final readonly class RegisterDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $device_name = null,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        return new static(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            device_name: $request->header('User-Agent'),
        );
    }
}
