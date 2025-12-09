<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthService
{
    /**
     * Yeni kullanıcı kaydı ve token oluşturma işlemi.
     * Array dönüş tipi {user: User, token: string} şeklindedir.
     */
    public function register(RegisterDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // 1. Kullanıcıyı oluştur
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);

            // 2. Sanctum token oluştur
            // 'auth_token' ismini veriyoruz.
            $token = $user->createToken('auth_token')->plainTextToken;

            // 3. Sonucu döndür
            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }
}
