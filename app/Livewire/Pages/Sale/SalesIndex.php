<?php

namespace App\Livewire\Pages\Sale;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SalesIndex extends Component
{
    use WithPagination;

    public string $date_from = '';

    public string $date_to = '';

    public function mount(): void
    {
        $this->date_from = now()->subMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function render()
    {
        $query = Sale::query()->with(['user', 'items.product'])
            ->when($this->date_from, fn ($q) => $q->whereDate('sold_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('sold_at', '<=', $this->date_to));

        $sales = $query->orderByDesc('sold_at')->orderByDesc('id')->paginate(10);

        return view('livewire.pages.sales.index', [
            'sales' => $sales,
        ]);
    }
}
