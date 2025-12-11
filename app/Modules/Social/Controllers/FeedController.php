<?php

declare(strict_types=1);

namespace App\Modules\Social\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Social\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(
        protected FeedService $feedService
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Modules\User\Models\User $user */
        $user = $request->user();

        $events = $this->feedService->getUserFeed($user);

        return response()->json([
            'data' => $events,
        ]);
    }
}
