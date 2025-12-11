<?php

declare(strict_types=1);

namespace App\Modules\Venue\Controllers;

use App\Contracts\Services\TicketTransferServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Resources\TransferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueTransferController extends Controller
{
    public function __construct(
        protected TicketTransferServiceInterface $transferService
    ) {}

    public function approve(Request $request, int $id): JsonResponse
    {
        $transfer = TicketTransfer::findOrFail($id);
        $user = $request->user();

        $this->transferService->approveByVenue($user, $transfer);

        return response()->json([
            'message' => 'Transfer approved by venue. Waiting for buyer acceptance.',
            'data' => TransferResource::make($transfer->fresh())->resolve(),
        ], 200);
    }
}
