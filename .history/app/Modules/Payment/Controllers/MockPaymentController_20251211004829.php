<?php

declare(strict_types=1);

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Transfer\Services\TicketTransferService;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Enums\TransferStatus;

class MockPaymentController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected TicketTransferService $transferService
    ) {}

    public function pay(Request $request): JsonResponse
    {
        // Validasyon: Ya order_id ya transfer_id gelmeli
        $validated = $request->validate([
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'transfer_id' => ['nullable', 'integer', 'exists:ticket_transfers,id'],
        ]);

        if (isset($validated['order_id'])) {
            return $this->processOrderPayment($request, (int) $validated['order_id']);
        }

        if (isset($validated['transfer_id'])) {
            return $this->processTransferPayment($request, (int) $validated['transfer_id']);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    private function processOrderPayment(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== OrderStatus::PENDING) {
            return response()->json(['message' => 'Order is not pending'], 400);
        }

        $this->orderService->completeOrder($order);

        return response()->json([
            'message' => 'Payment successful. Tickets generated.',
            'status' => 'paid',
        ]);
    }

    private function processTransferPayment(Request $request, int $id): JsonResponse
    {
        $transfer = TicketTransfer::findOrFail($id);

        // Ödemeyi yapan kişi, transferin ALICISI olmalı
        if ($transfer->to_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. Only the buyer can pay.'], 403);
        }

        if ($transfer->status !== TransferStatus::PENDING_PAYMENT) {
            return response()->json(['message' => 'Transfer is not ready for payment'], 400);
        }

        // Transferi tamamla
        $this->transferService->completeTransfer($transfer);

        return response()->json([
            'message' => 'Payment successful. Ticket transferred to your account.',
            'status' => 'completed',
        ]);
    }
}
