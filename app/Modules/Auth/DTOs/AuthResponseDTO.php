<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Modules\User\Models\User;

final readonly class AuthResponseDTO
{
    public function __construct(
        public User $user,
        public string $token,
    ) {}
}
