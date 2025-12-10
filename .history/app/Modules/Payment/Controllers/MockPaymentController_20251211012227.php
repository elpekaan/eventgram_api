<?php

declare(strict_types=1);

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Services\OrderService;
use App\Modules\Payment\Models\PaymentTransaction; // <--- Yeni
use App\Modules\Transfer\Enums\TransferStatus;
use App\Modules\Transfer\Models\TicketTransfer;
use App\Modules\Transfer\Services\TicketTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockPaymentController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected TicketTransferService $transferService
    ) {}

    public function pay(Request $request): JsonResponse
    {
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

        // 1. ÖDEME KAYDI OLUŞTUR (Processing)
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'provider' => 'mock_provider',
            'amount' => $order->total_amount,
            'currency' => 'TRY',
            'status' => 'processing',
            'idempotency_key' => Str::uuid()->toString(),
        ]);

        // 2. İYZICO SİMÜLASYONU (Başarılı)
        // Burada gerçekte İyzico API'ye gidilip 3D Secure linki alınır.
        // Mock olduğu için direkt başarı kabul ediyoruz.
        $transaction->update([
            'status' => 'success',
            'transaction_id' => 'mock_' . Str::random(10),
            'completed_at' => now(),
        ]);

        // 3. SİPARİŞİ TAMAMLA (Transaction nesnesini gönderiyoruz)
        $this->orderService->completeOrder($order, $transaction);

        return response()->json([
            'message' => 'Payment successful. Transaction recorded.',
            'transaction_id' => $transaction->transaction_id,
        ]);
    }

    private function processTransferPayment(Request $request, int $id): JsonResponse
    {
        $transfer = TicketTransfer::findOrFail($id);

        if ($transfer->to_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($transfer->status !== TransferStatus::PENDING_PAYMENT) {
            return response()->json(['message' => 'Transfer is not ready for payment'], 400);
        }

        // 1. ÖDEME KAYDI OLUŞTUR
        $transaction = PaymentTransaction::create([
            'transfer_id' => $transfer->id,
            'user_id' => $request->user()->id,
            'provider' => 'mock_provider',
            'amount' => $transfer->asking_price, // Transferde asking_price ödenir
            'currency' => 'TRY',
            'status' => 'processing',
            'idempotency_key' => Str::uuid()->toString(),
        ]);

        // 2. SİMÜLASYON
        $transaction->update([
            'status' => 'success',
            'transaction_id' => 'mock_tr_' . Str::random(10),
            'completed_at' => now(),
        ]);

        // 3. TRANSFERİ TAMAMLA
        $this->transferService->completeTransfer($transfer, $transaction);

        return response()->json([
            'message' => 'Payment successful. Transfer completed.',
            'transaction_id' => $transaction->transaction_id,
        ]);
    }
}
