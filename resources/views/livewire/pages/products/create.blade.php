<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('products.index') }}" wire:navigate class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Novo Produto') }}
            </h2>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <form wire:submit="save" class="p-6 space-y-6">
                <div>
                    <x-input-label for="name" :value="__('Nome')" />
                    <x-text-input wire:model="name" id="name" class="mt-1 block w-full" type="text" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="sku" :value="__('SKU')" />
                    <x-text-input wire:model="sku" id="sku" class="mt-1 block w-full" type="text" required />
                    <x-input-error class="mt-2" :messages="$errors->get('sku')" />
                </div>
                <div>
                    <x-input-label for="description" :value="__('Descrição')" />
                    <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="price" :value="__('Preço (R$)')" />
                        <x-text-input wire:model="price" id="price" class="mt-1 block w-full" type="number" step="0.01" min="0" required />
                        <x-input-error class="mt-2" :messages="$errors->get('price')" />
                    </div>
                    <div>
                        <x-input-label for="quantity" :value="__('Quantidade em estoque')" />
                        <x-text-input wire:model="quantity" id="quantity" class="mt-1 block w-full" type="number" min="0" required />
                        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                    </div>
                    <div>
                        <x-input-label for="min_stock" :value="__('Estoque mínimo')" />
                        <x-text-input wire:model="min_stock" id="min_stock" class="mt-1 block w-full" type="number" min="0" required />
                        <x-input-error class="mt-2" :messages="$errors->get('min_stock')" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <x-primary-button type="submit">{{ __('Salvar') }}</x-primary-button>
                    <a href="{{ route('products.index') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                        {{ __('Cancelar') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
