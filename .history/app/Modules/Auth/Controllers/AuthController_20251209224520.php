<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    // Dependency Injection ile Service'i alıyoruz.
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        // 1. Validate edilmiş veriyi DTO'ya çevir.
        $dto = RegisterDTO::fromRequest($request);

        // 2. Service katmanını kullanarak kayıt işlemini yap.
        $result = $this->authService->register($dto);

        // 3. Json Dön
        return response()->json([
            'message' => 'User registered successfully',
            'data' => $result
        ], 201); // 201 Created
    }

    public 
}
