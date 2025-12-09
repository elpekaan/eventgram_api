<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string
    ) {}
}
