<?php

use App\Http\Controllers\SaleController;
use App\Livewire\Dashboard;
use App\Livewire\Pages\Product\ProductCreate;
use App\Livewire\Pages\Product\ProductEdit;
use App\Livewire\Pages\Product\ProductIndex;
use App\Livewire\Pages\Sale\SaleCreate;
use App\Livewire\Pages\Sale\SalesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('products', ProductIndex::class)->name('products.index');
    Route::get('products/create', ProductCreate::class)->name('products.create');
    Route::get('products/{product}/edit', ProductEdit::class)->name('products.edit');
    Route::get('sales', SalesIndex::class)->name('sales.index');
    Route::get('sales/create', SaleCreate::class)->name('sales.create');
    Route::get('sales/export', [SaleController::class, 'export'])->name('sales.export');
});

require __DIR__.'/auth.php';
