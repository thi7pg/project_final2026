<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\ValidateCartRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function validateCart(ValidateCartRequest $request): JsonResponse
    {
        $this->cartService->resolveTable($request->validated('qr_token'));

        $cart = $this->cartService->buildCart($request->validated('items'));

        return $this->success(new CartResource($cart));
    }
}
