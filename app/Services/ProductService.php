<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function listPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = Product::query()
            ->with('latestPrediction')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('sku', 'like', '%'.$search.'%'));

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return Product::create([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => (int) $data['quantity'],
            'minimum_stock' => (int) ($data['min_stock'] ?? 0),
        ]);
    }

    public function update(Product $product, array $data): void
    {
        $product->update([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => (int) $data['quantity'],
            'minimum_stock' => (int) ($data['min_stock'] ?? 0),
        ]);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
    }
}
