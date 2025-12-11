<?php

declare(strict_types=1);

namespace App\Modules\Event\Controllers;

use App\Contracts\Services\EventServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Event\DTOs\CreateEventDTO;
use App\Modules\Event\Requests\CreateEventRequest;
use App\Modules\Event\Resources\EventResource;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        protected EventServiceInterface $eventService
    ) {}

    public function store(CreateEventRequest $request): JsonResponse
    {
        $dto = CreateEventDTO::fromRequest($request);
        $event = $this->eventService->create($dto);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => EventResource::make($event)->resolve(),
        ], 201);
    }
}
