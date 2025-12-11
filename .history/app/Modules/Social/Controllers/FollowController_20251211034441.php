<?php

declare(strict_types=1);

namespace App\Modules\Social\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Social\Services\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService
    ) {}

    public function toggleVenue(Request $request, int $id): JsonResponse
    {
        /** @var \App\Modules\User\Models\User $user */
        $user = $request->user();

        $result = $this->followService->toggleFollowVenue($user, $id);

        return response()->json($result);
    }
}
