<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categories->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return $this->categories->allActive();
    }

    public function find(int $id): ?Category
    {
        return $this->categories->find($id);
    }

    public function create(array $data): Category
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        $category = $this->categories->create($data);

        $this->activityLog->log('category.created', "Category {$category->name} created", $category);

        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        if (! empty($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        $category = $this->categories->update($category, $data);

        $this->activityLog->log('category.updated', "Category {$category->name} updated", $category, properties: $data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $this->activityLog->log('category.deleted', "Category {$category->name} deleted", $category);

        $this->categories->delete($category);
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
