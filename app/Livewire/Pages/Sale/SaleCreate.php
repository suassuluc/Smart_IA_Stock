<?php

namespace App\Livewire\Pages\Sale;

use App\Models\Product;
use App\Services\SaleService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleCreate extends Component
{
    public string $sold_at = '';

    /** @var array<int, array{product_id: string, quantity: string, unit_price: string}> */
    public array $items = [];

    public function mount(): void
    {
        $this->sold_at = now()->format('Y-m-d');
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => '1',
            'unit_price' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key): void
    {
        if (! str_contains((string) $key, '.product_id')) {
            return;
        }
        $idx = (int) explode('.', (string) $key)[0];
        $product = Product::find($this->items[$idx]['product_id'] ?? null);
        if ($product) {
            $this->items[$idx]['unit_price'] = (string) $product->price;
        }
    }

    public function rules(): array
    {
        return [
            'sold_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        try {
            app(SaleService::class)->createWithItems(
                $this->sold_at,
                $this->items,
                auth()->id()
            );
        } catch (\RuntimeException $e) {
            $this->addError('items', $e->getMessage());

            return;
        }

        session()->flash('message', 'Venda registrada com sucesso.');
        $this->redirect(route('sales.index'), navigate: true);
    }

    public function getProductsProperty()
    {
        return Product::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.pages.sales.create');
    }
}
