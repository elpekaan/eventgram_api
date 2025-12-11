<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\AuthResource;
use App\Modules\Auth\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Auth Endpoints
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    /**
     * Register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = RegisterDTO::fromRequest($request);
        $result = $this->authService->register($dto);

        return response()->json([
            'message' => 'User registered successfully',
            'data' => AuthResource::make($result)->resolve(),
        ], 201);
    }

    /**
     * Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromRequest($request);
        $result = $this->authService->login($dto);

        return response()->json([
            'message' => 'User logged in successfully',
            'data' => AuthResource::make($result)->resolve(),
        ], 200);
    }

    /**
     * Me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Authenticated user retrieved',
            'data' => UserResource::make($request->user())->resolve(),
        ], 200);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user()->id);

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
