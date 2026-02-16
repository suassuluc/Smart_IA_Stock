<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Vendas') }}
            </h2>
            <a href="{{ route('sales.create') }}" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                {{ __('Nova Venda') }}
            </a>
        </div>

        @if (session('message'))
            <div class="mb-4 p-4 rounded-md bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex flex-wrap gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Data de</label>
                        <x-text-input wire:model.live="date_from" type="date" class="block" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Data até</label>
                        <x-text-input wire:model.live="date_to" type="date" class="block" />
                    </div>
                    <div class="flex items-end">
                        <a href="{{ route('sales.export') }}?date_from={{ urlencode($date_from) }}&date_to={{ urlencode($date_to) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-semibold text-sm text-white no-underline shadow-md hover:shadow-lg transition-all duration-200 bg-[#16a34a] hover:bg-[#15803d]">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            Exportar Excel
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed sm:table-auto ">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Usuário</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Itens</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $sale->sold_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $sale->user?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        @foreach ($sale->items as $item)
                                            <span class="block">{{ $item->product->name }} x{{ $item->quantity }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Nenhuma venda no período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
