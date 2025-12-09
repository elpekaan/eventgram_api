<?php

declare(strict_types=1);

namespace App\Modules\Venue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Venue\Enums\VenueStatus;
use App\Modules\Venue\Models\Venue;
use App\Modules\Venue\Services\VenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminVenueController extends Controller
{
    public function __construct(
        protected VenueService $venueService
    ) {}

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        // 1. Validasyon (Sadece enum değerleri gelebilir)
        $validated = $request->validate([
            'status' => ['required', Rule::enum(VenueStatus::class)],
        ]);

        // 2. Mekanı bul
        $venue = Venue::findOrFail($id);

        // 3. Statüyü güncelle
        $status = VenueStatus::from($validated['status']);
        $updatedVenue = $this->venueService->updateStatus($venue, $status);

        return response()->json([
            'message' => "Venue status updated to {$status->value}",
            'data' => $updatedVenue,
        ]);
    }
}
