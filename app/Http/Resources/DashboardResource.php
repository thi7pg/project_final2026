<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $summary = $this->resource;

        return [
            'today_orders' => $summary['today_orders'],
            'today_revenue' => (float) $summary['today_revenue'],
            'pending_orders' => $summary['pending_orders'],
            'preparing_orders' => $summary['preparing_orders'],
            'completed_orders' => $summary['completed_orders'],
            'popular_menu' => PopularProductResource::collection($summary['popular_menu']),
            'recent_orders' => OrderResource::collection($summary['recent_orders']),
        ];
    }
}
