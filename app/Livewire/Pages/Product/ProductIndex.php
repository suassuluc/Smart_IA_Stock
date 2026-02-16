<?php

namespace App\Livewire\Pages\Product;

use App\Services\ProductService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function delete(int $id): void
    {
        app(ProductService::class)->delete($id);
        session()->flash('message', 'Produto excluído.');
    }

    public function render()
    {
        $products = app(ProductService::class)->listPaginated($this->search, 10);

        return view('livewire.pages.products.index', [
            'products' => $products,
        ]);
    }
}
