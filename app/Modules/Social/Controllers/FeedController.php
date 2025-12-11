<?php

declare(strict_types=1);

namespace App\Modules\Social\Controllers;

use App\Contracts\Services\FeedServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Event\Resources\EventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(
        protected FeedServiceInterface $feedService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $events = $this->feedService->getUserFeed($user);

        return response()->json([
            'message' => 'Feed retrieved successfully',
            'data' => EventResource::collection($events)->resolve(),
        ], 200);
    }
}
