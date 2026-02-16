<?php

use App\Livewire\Pages\Product\ProductEdit;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('permite editar produto', function () {
    $product = Product::factory()->create(['name' => 'Original', 'stock_quantity' => 5]);

    $this->actingAs($this->user);

    Livewire::actingAs($this->user)
        ->test(ProductEdit::class, ['product' => $product])
        ->set('name', 'Produto Atualizado')
        ->set('quantity', '20')
        ->call('save')
        ->assertRedirect(route('products.index'));

    $product->refresh();
    expect($product->name)->toBe('Produto Atualizado');
    expect($product->stock_quantity)->toBe(20);
});
