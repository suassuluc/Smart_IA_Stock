<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = [
        'sold_at',
        'total',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->items->sum('subtotal');
        $this->saveQuietly();
    }
}
