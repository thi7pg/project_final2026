<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Contracts\Repositories\TableRepositoryInterface;
use App\Exceptions\ApiException;
use App\Models\DiningTable;

class CartService
{
    public function __construct(
        protected TableRepositoryInterface $tables,
        protected ProductRepositoryInterface $products,
        protected RestaurantRepositoryInterface $restaurants,
    ) {}

    /**
     * Resolve a table by QR token, or throw a 404 ApiException.
     */
    public function resolveTable(string $qrToken): DiningTable
    {
        $table = $this->tables->findByQrToken($qrToken);

        if (! $table || $table->status === DiningTable::STATUS_INACTIVE) {
            throw new ApiException('Invalid or inactive QR code.', 404);
        }

        return $table;
    }

    /**
     * Validate cart items and compute server-authoritative pricing.
     * Never trust client-supplied prices.
     *
     * @param  array<int, array{product_id: int, quantity: int, notes?: string|null}>  $items
     * @return array{items: array, subtotal: float, tax_amount: float, service_charge_amount: float, total_amount: float}
     */
    public function buildCart(array $items): array
    {
        if (empty($items)) {
            throw new ApiException('Cart cannot be empty.', 422, ['items' => ['At least one item is required.']]);
        }

        $productIds = array_column($items, 'product_id');
        $availableProducts = $this->products->findAvailableByIds($productIds)->keyBy('id');

        $unavailable = array_values(array_diff($productIds, $availableProducts->keys()->all()));

        if (! empty($unavailable)) {
            throw new ApiException(
                'One or more products are unavailable.',
                422,
                ['product_ids' => [sprintf('Products not available: %s', implode(', ', $unavailable))]]
            );
        }

        $lineItems = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $product = $availableProducts->get($item['product_id']);
            $quantity = (int) $item['quantity'];
            $lineTotal = round((float) $product->price * $quantity, 2);
            $subtotal += $lineTotal;

            $lineItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => (float) $product->price,
                'subtotal' => $lineTotal,
                'notes' => $item['notes'] ?? null,
            ];
        }

        $restaurant = $this->restaurants->getSettings();
        $subtotal = round($subtotal, 2);
        $taxAmount = round($subtotal * ((float) $restaurant->tax_percentage / 100), 2);
        $serviceChargeAmount = round($subtotal * ((float) $restaurant->service_charge_percentage / 100), 2);
        $totalAmount = round($subtotal + $taxAmount + $serviceChargeAmount, 2);

        return [
            'items' => $lineItems,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge_amount' => $serviceChargeAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
