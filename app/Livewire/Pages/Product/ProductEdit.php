<?php

namespace App\Livewire\Pages\Product;

use App\Models\Product;
use App\Services\ProductService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductEdit extends Component
{
    public Product $product;

    public string $name = '';

    public string $sku = '';

    public string $description = '';

    public string $price = '';

    public string $quantity = '0';

    public string $min_stock = '0';

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->quantity = (string) $product->stock_quantity;
        $this->min_stock = (string) $product->minimum_stock;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.$this->product->id],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        app(ProductService::class)->update($this->product, [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'min_stock' => $this->min_stock,
        ]);

        session()->flash('message', 'Produto atualizado com sucesso.');
        $this->redirect(route('products.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.products.edit');
    }
}
