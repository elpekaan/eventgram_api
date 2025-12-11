<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\AuthResponseDTO;

interface AuthServiceInterface
{
    public function register(RegisterDTO $dto): AuthResponseDTO;
    
    public function login(LoginDTO $dto): AuthResponseDTO;
    
    public function logout(int $userId): void;
}
