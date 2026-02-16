<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        if (Product::count() === 0) {
            $this->command->warn('Nenhum produto encontrado. Execute ProductSeeder antes.');

            return;
        }

        for ($i = 0; $i < 30; $i++) {
            $sale = Sale::create([
                'sold_at' => now()->subDays(fake()->numberBetween(1, 365)),
                'total' => 0,
                'user_id' => $user->id,
            ]);

            $productsWithStock = Product::where('stock_quantity', '>', 0)->get();
            $numItems = $productsWithStock->isEmpty() ? 0 : fake()->numberBetween(1, min(3, $productsWithStock->count()));

            for ($j = 0; $j < $numItems; $j++) {
                $product = $productsWithStock->random();
                $qty = fake()->numberBetween(1, min(3, $product->stock_quantity));
                $unitPrice = $product->price;
                $subtotal = round($qty * $unitPrice, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $productsWithStock = Product::where('stock_quantity', '>', 0)->get();
            }

            $sale->recalculateTotal();
        }
    }
}
