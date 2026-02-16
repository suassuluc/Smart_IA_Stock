<?php

use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('exporta vendas em excel com filtro de data', function () {
    Sale::factory()->create([
        'user_id' => $this->user->id,
        'sold_at' => now()->subDays(5),
    ]);

    $this->actingAs($this->user);
    $response = $this->get(route('sales.export', [
        'date_from' => now()->subDays(10)->format('Y-m-d'),
        'date_to' => now()->format('Y-m-d'),
    ]));

    $response->assertSuccessful();
    $response->assertDownload();
});
