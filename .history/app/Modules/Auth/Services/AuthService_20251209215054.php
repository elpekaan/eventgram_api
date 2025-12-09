<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\User\Models\User;

class AuthService {

    /**
     * Yeni kullanıcı kaydı ve token oluşturma işlemi.
     * Array dönüş tipi {user: User, token: string} şeklindedir.
     */

    public function register (RegisterDTO $dto): array {
        // Kayıt işlemi burada yapılacak
        return DB::transaction(function () use ($dto) {
            // 1. Kullanıcı oluşturma
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);
            //*
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }

}
