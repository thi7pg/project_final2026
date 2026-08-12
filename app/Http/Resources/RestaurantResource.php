<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo ? asset('storage/'.$this->logo) : null,
            'currency' => $this->currency,
            'tax_percentage' => (float) $this->tax_percentage,
            'service_charge_percentage' => (float) $this->service_charge_percentage,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
        ];
    }
}
