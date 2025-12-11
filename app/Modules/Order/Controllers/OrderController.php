<?php

declare(strict_types=1);

namespace App\Modules\Order\Controllers;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Modules\Order\DTOs\CreateOrderDTO;
use App\Modules\Order\Requests\CreateOrderRequest;
use App\Modules\Order\Resources\OrderResource;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $dto = CreateOrderDTO::fromRequest($request);
        $order = $this->orderService->createOrder($dto);

        return response()->json([
            'message' => 'Order created successfully. Please proceed to payment.',
            'data' => OrderResource::make($order)->resolve(),
            'payment_url' => route('payment.checkout', ['order' => $order->id]),
        ], 201);
    }
}
