<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Produtos') }}
            </h2>
            <a href="{{ route('products.create') }}" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                {{ __('Novo Produto') }}
            </a>
        </div>
        @if (session('message'))
            <div class="mb-4 p-4 rounded-md bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-4">
                    <x-text-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nome ou SKU..." class="block w-full max-w-md" />
                </div>

                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700 table-fixed sm:table-auto">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">SKU</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Preço</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Estoque</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">{{ __('Previsão') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5 sm:w-auto">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $product->sku }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm {{ $product->isLowStock() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-gray-100' }}">
                                            {{ $product->stock_quantity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        @php $days = $product->daysUntilStockOut(); @endphp
                                        @if ($days !== null)
                                            @if ($days <= 0)
                                                {{ __('Esgotado / em breve') }}
                                            @else
                                                {{ __('~:count dias', ['count' => $days]) }}
                                                <span class="text-gray-400 dark:text-gray-500">({{ $product->latestPrediction?->predicted_until?->format('d/m/Y') }})</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('products.edit', $product) }}" wire:navigate class="text-indigo-600 dark:text-indigo-400 hover:underline me-3">Editar</a>
                                        <button wire:click="delete({{ $product->id }})" wire:confirm="Excluir este produto?"
                                                class="text-red-600 dark:text-red-400 hover:underline">
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Nenhum produto encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
