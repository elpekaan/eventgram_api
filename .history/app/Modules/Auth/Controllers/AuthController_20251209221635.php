<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    // Dependency Injection ile Service'i alıyoruz.
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(R)
}
