<?php

declare(strict_types=1);

namespace App\Modules\Venue\Controllers;

use App\Contracts\Services\VenueServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Venue\Models\Venue;
use App\Modules\Venue\Resources\VenueResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminVenueController extends Controller
{
    public function __construct(
        protected VenueServiceInterface $venueService
    ) {}

    public function approve(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $venue = $this->venueService->approve($id, $request->user()->id, $validated['notes']);

        return response()->json([
            'message' => 'Venue approved successfully',
            'data' => VenueResource::make($venue)->resolve(),
        ], 200);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $venue = $this->venueService->reject($id, $request->user()->id, $validated['reason']);

        return response()->json([
            'message' => 'Venue rejected',
            'data' => VenueResource::make($venue)->resolve(),
        ], 200);
    }
}
