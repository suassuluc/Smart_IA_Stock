<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('quantity', 'stock_quantity');
            $table->renameColumn('min_stock', 'minimum_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('stock_quantity', 'quantity');
            $table->renameColumn('minimum_stock', 'min_stock');
        });
    }
};
