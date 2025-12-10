<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\Request;

/**
 * @group Auth Endpoints
 *
 * Kullanıcı kayıt, giriş ve profil işlemleri.
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Kullanıcı Kaydı (Register)
     *
     * Sisteme yeni bir kullanıcı kaydeder ve erişim tokenı döner.
     *
     * @response 201 {
     *   "message": "User registered successfully",
     *   "data": {
     *     "user": { "id": 1, "name": "Ali", "email": "ali@test.com" },
     *     "token": "1|AbCdEf..."
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = RegisterDTO::fromRequest($request);
        $result = $this->authService->register($dto);

        return response()->json([
            'message' => 'User registered successfully',
            'data' => $result
        ], 201);
    }

    /**
     * Kullanıcı Girişi (Login)
     *
     * Mevcut kullanıcıyı doğrular ve token verir.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromRequest($request);
        $result = $this->authService->login($dto);

        return response()->json([
            'message' => 'User logged in successfully',
            'data' => $result
        ], 200);
    }

    /**
     * Profil Bilgisi (Me)
     *
     * Giriş yapmış kullanıcının bilgilerini döner.
     * Token gerektirir.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Authenticated user retrieved successfully',
            'data' => $request->user()
        ], 200);
    }
}
