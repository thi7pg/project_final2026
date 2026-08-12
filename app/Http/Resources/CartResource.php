<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps the computed cart array from CartService::buildCart().
 */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cart = $this->resource;

        return [
            'items' => collect($cart['items'])->map(fn ($line) => [
                'product_id' => $line['product']->id,
                'product_name' => $line['product']->name,
                'unit_price' => $line['unit_price'],
                'quantity' => $line['quantity'],
                'subtotal' => $line['subtotal'],
                'notes' => $line['notes'],
            ])->all(),
            'subtotal' => $cart['subtotal'],
            'tax_amount' => $cart['tax_amount'],
            'service_charge_amount' => $cart['service_charge_amount'],
            'total_amount' => $cart['total_amount'],
        ];
    }
}
