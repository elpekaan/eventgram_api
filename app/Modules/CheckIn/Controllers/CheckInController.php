<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Controllers;

use App\Contracts\Services\CheckInServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\CheckIn\DTOs\CheckInRequestDTO;
use App\Modules\CheckIn\Requests\CheckInRequest;
use App\Modules\CheckIn\Resources\CheckInResource;
use Illuminate\Http\JsonResponse;

class CheckInController extends Controller
{
    public function __construct(
        protected CheckInServiceInterface $checkInService
    ) {}

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $dto = CheckInRequestDTO::fromRequest($request);
        $result = $this->checkInService->checkIn($dto);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => CheckInResource::make($result)->resolve(),
        ], 200);
    }
}
