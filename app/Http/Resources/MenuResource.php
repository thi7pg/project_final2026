<?php

namespace App\Http\Resources;

use App\Models\DiningTable;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property array{restaurant: Restaurant, table: DiningTable, categories: Collection} $resource
 */
class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'restaurant' => new RestaurantResource($this->resource['restaurant']),
            'table' => [
                'id' => $this->resource['table']->id,
                'table_number' => $this->resource['table']->table_number,
                'capacity' => $this->resource['table']->capacity,
            ],
            'categories' => CategoryResource::collection($this->resource['categories']),
        ];
    }
}
