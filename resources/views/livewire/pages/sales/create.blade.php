<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('sales.index') }}" wire:navigate class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Nova Venda') }}
            </h2>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <form wire:submit="save" class="p-6 space-y-6">
                <div class="max-w-xs">
                    <x-input-label for="sold_at" :value="__('Data da venda')" />
                    <x-text-input wire:model="sold_at" id="sold_at" class="mt-1 block w-full" type="date" required />
                    <x-input-error class="mt-2" :messages="$errors->get('sold_at')" />
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <x-input-label :value="__('Itens da venda')" />
                        <button type="button" wire:click="addItem" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            + Adicionar item
                        </button>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('items')" />

                    <div class="space-y-4">
                        @foreach ($items as $index => $item)
                            <div class="flex flex-wrap items-end gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg" wire:key="item-{{ $index }}">
                                <div class="flex-1 min-w-[180px]">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Produto</label>
                                    <select wire:model.live="items.{{ $index }}.product_id" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Selecione...</option>
                                        @foreach ($this->products as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }}) - Estoque: {{ $p->stock_quantity }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Qtd</label>
                                    <x-text-input wire:model="items.{{ $index }}.quantity" type="number" min="1" class="block w-full" />
                                </div>
                                <div class="w-28">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Preço un.</label>
                                    <x-text-input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" class="block w-full" />
                                </div>
                                <div class="w-10">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3">
                    <x-primary-button type="submit">{{ __('Registrar venda') }}</x-primary-button>
                    <a href="{{ route('sales.index') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                        {{ __('Cancelar') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
