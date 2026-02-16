<?php

use App\Livewire\Pages\Product\ProductIndex;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lista produtos para usuário autenticado', function () {
    Product::factory()->count(3)->create();

    $this->actingAs($this->user);
    $response = $this->get(route('products.index'));

    $response->assertOk();
    $response->assertSee('Produtos');
});

it('permite excluir produto', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->user);

    Livewire::actingAs($this->user)
        ->test(ProductIndex::class)
        ->call('delete', $product->id);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
