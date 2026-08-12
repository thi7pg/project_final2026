<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'table' => $this->whenLoaded('table', fn () => [
                'id' => $this->table->id,
                'table_number' => $this->table->table_number,
            ]),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'service_charge_amount' => (float) $this->service_charge_amount,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'cancelled_reason' => $this->cancelled_reason,
            'confirmed_at' => $this->confirmed_at,
            'preparing_at' => $this->preparing_at,
            'ready_at' => $this->ready_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
        ];
    }
}
