<?php

use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lista vendas com filtro de data para usuário autenticado', function () {
    Sale::factory()->count(2)->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user);
    $response = $this->get(route('sales.index'));

    $response->assertOk();
    $response->assertSee('Vendas');
});
