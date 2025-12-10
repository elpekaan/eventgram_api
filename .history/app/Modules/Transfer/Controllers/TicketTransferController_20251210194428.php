<?php

declare(strict_types=1);

namespace App\Modules\Transfer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Transfer\DTOs\CreateTransferDTO;
use App\Modules\Transfer\Requests\CreateTransferRequest;
use App\Modules\Transfer\Services\TicketTransferService;
use Illuminate\Http\JsonResponse;

class TicketTransferController extends Controller
{
    public function __construct(
        protected TicketTransferService $transferService
    ) {}

    public function store(CreateTransferRequest $request): JsonResponse
    {
        // 1. DTO
        $dto = CreateTransferDTO::fromRequest($request);

        // 2. Service
        // Request'i yapan kullanıcı (Satıcı) ile servisi çağırıyoruz.
        /** @var \App\Modules\User\Models\User $seller */
        $seller = $request->user();

        $transfer = $this->transferService->createTransfer($seller, $dto);

        // 3. Response
        return response()->json([
            'message' => 'Transfer request created successfully. Waiting for venue approval.',
            'data' => $transfer,
        ], 201);
    }
}
