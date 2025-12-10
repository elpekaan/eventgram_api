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
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Sadece kendi siparişini ödeyebilir (veya admin)
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== OrderStatus::PENDING) {
            return response()->json(['message' => 'Order is not pending'], 400);
        }

        // Ödeme Başarılı Simülasyonu
        $this->orderService->completeOrder($order);

        return response()->json([
            'message' => 'Payment successful. Tickets generated.',
            'order_status' => $order->refresh()->status,
        ]);
    }
}
