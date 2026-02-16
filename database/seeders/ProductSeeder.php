<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Product::factory()->create([
                'stock_quantity' => fake()->numberBetween(10, 100),
            ]);
        }
    }
}
