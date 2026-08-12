<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Contracts\Repositories\TableRepositoryInterface;
use App\Exceptions\ApiException;
use App\Models\DiningTable;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Collection;

class MenuService
{
    public function __construct(
        protected TableRepositoryInterface $tables,
        protected CategoryRepositoryInterface $categories,
        protected RestaurantRepositoryInterface $restaurants,
    ) {}

    /**
     * @return array{restaurant: Restaurant, table: DiningTable, categories: Collection}
     */
    public function getMenu(string $qrToken): array
    {
        $table = $this->tables->findByQrToken($qrToken);

        if (! $table || $table->status === DiningTable::STATUS_INACTIVE) {
            throw new ApiException('Invalid or inactive QR code.', 404);
        }

        $categories = $this->categories->allActive()->load([
            'products' => fn ($query) => $query->where('available', true),
        ]);

        return [
            'restaurant' => $this->restaurants->getSettings(),
            'table' => $table,
            'categories' => $categories,
        ];
    }
}
