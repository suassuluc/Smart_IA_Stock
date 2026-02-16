<?php

use App\Livewire\Pages\Sale\SaleCreate;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('registra venda e atualiza estoque automaticamente', function () {
    $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 15.00]);

    $this->actingAs($this->user);

    Livewire::actingAs($this->user)
        ->test(SaleCreate::class)
        ->set('sold_at', now()->format('Y-m-d'))
        ->set('items', [
            [
                'product_id' => (string) $product->id,
                'quantity' => '3',
                'unit_price' => '15.00',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('sales.index'));

    $this->assertDatabaseHas('sales', ['user_id' => $this->user->id]);
    $sale = Sale::latest()->first();
    expect($sale->total)->toBe('45.00');

    $product->refresh();
    expect($product->stock_quantity)->toBe(7);
});
