<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TrackOrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->placeOrder(
            $request->validated('qr_token'),
            $request->validated('customer_name'),
            $request->validated('customer_phone'),
            $request->validated('items'),
            $request->validated('notes'),
        );

        return $this->success(new OrderResource($order), 'Order placed successfully.', 201);
    }

    public function track(string $orderNumber): JsonResponse
    {
        $order = $this->orderService->findByOrderNumber($orderNumber);

        return $this->success(new TrackOrderResource($order));
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'table_id', 'date']);

        return $this->success(OrderResource::collection($this->orderService->paginate($filters)));
    }

    public function show(Order $order): JsonResponse
    {
        return $this->success(new OrderResource($this->orderService->find($order->id)));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->updateStatus(
            $order,
            $request->validated('status'),
            $request->validated('cancelled_reason'),
        );

        return $this->success(new OrderResource($order), 'Order status updated successfully.');
    }
}
