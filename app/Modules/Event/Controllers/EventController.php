<?php

declare(strict_types=1);

namespace App\Modules\Event\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Event\DTOs\CreateEventDTO;
use App\Modules\Event\Requests\CreateEventRequest;
use App\Modules\Event\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function store(CreateEventRequest $request): JsonResponse
    {
        // 1. DTO
        $dto = CreateEventDTO::fromRequest($request);

        // 2. Service
        $event = $this->eventService->create($dto);

        // 3. Response
        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event,
        ], 201);
    }
}
