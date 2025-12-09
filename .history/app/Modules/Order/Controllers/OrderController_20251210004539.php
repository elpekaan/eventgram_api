<?php

declare(strict_types=1);

namespace App\Modules\Order\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\DTOs\CreateOrderDTO;
use App\Modules\Order\Requests\CreateOrderRequest;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        // 1. DTO
        $dto = CreateOrderDTO::fromRequest($request);

        // 2. Service (Transaction & Locking burada)
        $order = $this->orderService->createOrder($dto);

        // 3. Response
        return response()->json([
            'message' => 'Order created successfully. Please proceed to payment.',
            'data' => $order,
            'payment_url' => 'https://mock-payment.com/checkout/' . $order->id, // İleride İyzico linki olacak
        ], 201);
    }
}
