<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illumniate\Validation\ValidationException;

class AuthService
{
    /**
     * Kullanıcı gitişi ve token oluşturma.
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        // Kullanıcı bulunamazsa veya şifre yanlışsa hata fırlat
        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    }

    /**
     * Yeni kullanıcı kaydı ve token oluşturma işlemi.
     * Array dönüş tipi {user: User, token: string} şeklindedir.
     */
    public function register(RegisterDTO $dto): array
    {
        // Kayıt işlemi burada yapılacak
        return DB::transaction(function () use ($dto) {
            // 1. Kullanıcı oluşturma
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);
            // 2. Token oluşturma ('auth_token' adıyla)
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }
}
