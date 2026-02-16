<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null
    ) {}

    public function query()
    {
        $query = Sale::query()->with(['user', 'items.product']);

        if ($this->dateFrom) {
            $query->whereDate('sold_at', '>=', Carbon::parse($this->dateFrom));
        }
        if ($this->dateTo) {
            $query->whereDate('sold_at', '<=', Carbon::parse($this->dateTo));
        }

        return $query->orderBy('sold_at')->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Data',
            'Total (R$)',
            'Usuário',
            'Itens (resumo)',
        ];
    }

    public function map($sale): array
    {
        $itemsSummary = $sale->items->map(fn ($i) => $i->product->name . ' x' . $i->quantity)->join(', ');

        return [
            $sale->id,
            $sale->sold_at->format('d/m/Y'),
            number_format($sale->total, 2, ',', '.'),
            $sale->user?->name ?? '-',
            $itemsSummary,
        ];
    }
}
