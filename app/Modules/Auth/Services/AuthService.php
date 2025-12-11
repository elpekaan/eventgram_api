<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Contracts\Services\AuthServiceInterface;
use App\Modules\Auth\DTOs\AuthResponseDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Auth\Events\UserLoggedIn;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthService implements AuthServiceInterface
{
    /**
     * Register new user with email verification token
     */
    public function register(RegisterDTO $dto): AuthResponseDTO
    {
        return DB::transaction(function () use ($dto) {
            // 1. Create user
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'email_verification_token' => Str::random(64),
                'role' => 'user',
                'points' => 0,
                'level' => 1,
            ]);

            // 2. Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 3. Fire event
            event(new UserRegistered($user));

            // 4. Log
            Log::info('User registered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return new AuthResponseDTO(
                user: $user,
                token: $token,
            );
        });
    }

    /**
     * Login user with credentials
     * 
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): AuthResponseDTO
    {
        return DB::transaction(function () use ($dto) {
            // 1. Find user
            $user = User::where('email', $dto->email)->first();

            // 2. Validate credentials
            if (!$user || !Hash::check($dto->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // 3. Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 4. Fire event
            event(new UserLoggedIn(
                user: $user,
                ipAddress: request()->ip() ?? 'unknown',
                userAgent: request()->userAgent() ?? 'unknown',
            ));

            // 5. Log
            Log::info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);

            return new AuthResponseDTO(
                user: $user,
                token: $token,
            );
        });
    }

    /**
     * Logout user (revoke all tokens)
     */
    public function logout(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        // Revoke all tokens
        $user->tokens()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
        ]);
    }
}
