<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $products,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->products->paginate($filters, $perPage);
    }

    public function find(int $id): ?Product
    {
        return $this->products->find($id);
    }

    public function create(array $data): Product
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        $product = $this->products->create($data);

        $this->activityLog->log('product.created', "Product {$product->name} created", $product);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        if (! empty($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        $product = $this->products->update($product, $data);

        $this->activityLog->log('product.updated', "Product {$product->name} updated", $product, properties: $data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $this->activityLog->log('product.deleted', "Product {$product->name} deleted", $product);

        $this->products->delete($product);
    }

    public function popular(int $limit = 5, ?int $days = null): Collection
    {
        return $this->products->popular($limit, $days);
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
