<?php

declare(strict_types=1);

namespace App\Modules\Venue\Controllers;

use App\Contracts\Services\VenueServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Venue\DTOs\CreateVenueDTO;
use App\Modules\Venue\Requests\CreateVenueRequest;
use App\Modules\Venue\Resources\VenueResource;
use Illuminate\Http\JsonResponse;

class VenueController extends Controller
{
    public function __construct(
        protected VenueServiceInterface $venueService
    ) {}

    public function store(CreateVenueRequest $request): JsonResponse
    {
        $dto = CreateVenueDTO::fromRequest($request);
        $user = $request->user();

        $venue = $this->venueService->create($user, $dto);

        return response()->json([
            'message' => 'Venue created successfully. Waiting for approval.',
            'data' => VenueResource::make($venue)->resolve(),
        ], 201);
    }
}
