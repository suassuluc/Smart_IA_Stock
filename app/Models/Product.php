<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock_quantity',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /** Última previsão de esgotamento (uma por produto, atualizada pelo StockPredictionService). */
    public function latestPrediction(): HasOne
    {
        return $this->hasOne(Prediction::class)->latestOfMany('created_at');
    }

    /** Dias até esgotar segundo a última previsão; null se não houver previsão. */
    public function daysUntilStockOut(): ?int
    {
        $prediction = $this->latestPrediction;
        if (! $prediction || ! $prediction->predicted_until) {
            return null;
        }

        return (int) now()->diffInDays($prediction->predicted_until, false);
    }

    public function isLowStock(): bool
    {
        return $this->minimum_stock > 0 && $this->stock_quantity <= $this->minimum_stock;
    }

    public function hasStock(int $amount = 1): bool
    {
        return $this->stock_quantity >= $amount;
    }
}
