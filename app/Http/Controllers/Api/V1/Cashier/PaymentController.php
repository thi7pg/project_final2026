<?php

namespace App\Http\Controllers\Api\V1\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status']);

        return $this->success(PaymentResource::collection($this->paymentService->paginate($filters)));
    }

    public function pay(Request $request, Order $order): JsonResponse
    {
        $payment = $this->paymentService->markPaid($order, $request->user());

        return $this->success(new PaymentResource($payment), 'Payment marked as paid successfully.');
    }
}
