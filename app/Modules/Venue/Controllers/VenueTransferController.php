<?php

declare(strict_types=1);

namespace App\Modules\Venue\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Services\TicketTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueTransferController extends Controller
{
    public function __construct(
        protected TicketTransferService $transferService
    ) {}

    public function approve(Request $request, int $id): JsonResponse
    {
        // Transferi bul
        $transfer = TicketTransfer::findOrFail($id);

        // Service ile onayla
        /** @var \App\Modules\User\Models\User $user */
        $user = $request->user();

        $this->transferService->approveByVenue($user, $transfer);

        return response()->json([
            'message' => 'Transfer approved by venue. Waiting for buyer acceptance.',
            'data' => $transfer->fresh(),
        ]);
    }
}
