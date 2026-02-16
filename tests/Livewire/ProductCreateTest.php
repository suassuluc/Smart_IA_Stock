<?php

use App\Livewire\Pages\Product\ProductCreate;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('permite criar produto', function () {
    $this->actingAs($this->user);

    Livewire::actingAs($this->user)
        ->test(ProductCreate::class)
        ->set('name', 'Produto Teste')
        ->set('sku', 'SKU-001')
        ->set('price', '19.90')
        ->set('quantity', '10')
        ->set('min_stock', '2')
        ->call('save')
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'name' => 'Produto Teste',
        'sku' => 'SKU-001',
        'stock_quantity' => 10,
    ]);
});
