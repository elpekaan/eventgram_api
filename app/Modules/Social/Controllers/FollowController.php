<?php

declare(strict_types=1);

namespace App\Modules\Social\Controllers;

use App\Contracts\Services\FollowServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        protected FollowServiceInterface $followService
    ) {}

    public function toggleVenue(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->followService->toggleFollowVenue($user, $id);

        return response()->json([
            'message' => $result['message'],
            'action' => $result['action'],
        ], 200);
    }
}
