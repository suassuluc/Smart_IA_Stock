<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'sold_at' => now()->subDays(fake()->numberBetween(1, 365)),
            'total' => fake()->randomFloat(2, 10, 500),
            'user_id' => User::factory(),
        ];
    }
}
