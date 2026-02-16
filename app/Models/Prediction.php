<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    protected $fillable = [
        'product_id',
        'predicted_quantity',
        'predicted_until',
    ];

    protected function casts(): array
    {
        return [
            'predicted_quantity' => 'integer',
            'predicted_until' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
