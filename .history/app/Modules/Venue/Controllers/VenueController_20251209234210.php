<?php

declare(strict_types=1);

namespace App\Modules\Venue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Venue\DTOs\CreateVenueDTO;
use App\Modules\Venue\Requests\CreateVenueRequest;
use App\Modules\Venue\Services\VenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function __construct(
        protected VenueService $venueService
    ) {}

    public function store(CreateVenueRequest $request): JsonResponse
    {
        // 1. DTO oluştur
        $dto = CreateVenueDTO::fromRequest($request);

        // 2. Service çağır (User'ı request'ten alıyoruz)
        /** @var \App\Modules\User\Models\User $user */
        $user = $request->user();

        $venue = $this->venueService->create($user, $dto);

        // 3. Response dön
        return response()->json([
            'message' => 'Venue created successfully. Waiting for approval.',
            'data' => $venue,
        ], 201);
    }
}
