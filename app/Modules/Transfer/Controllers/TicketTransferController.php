<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Controllers;

use App\Contracts\Services\TicketTransferServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Transfer\DTOs\CreateTransferDTO;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Requests\CreateTransferRequest;
use App\Modules\Transfer\Resources\TransferResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketTransferController extends Controller
{
    public function __construct(
        protected TicketTransferServiceInterface $transferService
    ) {}

    public function store(CreateTransferRequest $request): JsonResponse
    {
        $dto = CreateTransferDTO::fromRequest($request);
        $seller = $request->user();

        $transfer = $this->transferService->createTransfer($seller, $dto);

        return response()->json([
            'message' => 'Transfer request created successfully. Waiting for venue approval.',
            'data' => TransferResource::make($transfer)->resolve(),
        ], 201);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $buyer = $request->user();
        $transfer = TicketTransfer::findOrFail($id);

        $this->transferService->acceptByBuyer($buyer, $transfer);

        return response()->json([
            'message' => 'Transfer accepted. Proceed to payment.',
            'data' => TransferResource::make($transfer->fresh())->resolve(),
        ], 200);
    }
}
