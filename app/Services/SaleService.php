<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

class SaleService
{
    /**
     * Cria uma venda com itens. Atualiza estoque automaticamente (via modelo SaleItem).
     *
     * @param  array<int, array{product_id: string, quantity: string, unit_price: string}>  $items
     * @throws \RuntimeException quando não houver estoque suficiente para algum item
     */
    public function createWithItems(string $saleDate, array $items, ?int $userId = null): Sale
    {
        foreach ($items as $row) {
            $product = Product::find($row['product_id']);
            if ($product && ! $product->hasStock((int) $row['quantity'])) {
                throw new \RuntimeException(
                    "Estoque insuficiente para \"{$product->name}\" (disponível: {$product->stock_quantity})."
                );
            }
        }

        $sale = Sale::create([
            'sold_at' => $saleDate,
            'total' => 0,
            'user_id' => $userId,
        ]);

        foreach ($items as $row) {
            $qty = (int) $row['quantity'];
            $unitPrice = (float) $row['unit_price'];
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $row['product_id'],
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $qty * $unitPrice,
            ]);
        }

        $sale->load('items');
        $sale->recalculateTotal();

        return $sale;
    }
}
