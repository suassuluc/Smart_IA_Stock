<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full sm:max-w-md">
    <!-- Card -->
    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-2xl rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-8 py-10 sm:px-10 sm:py-12">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                <div>
                    <x-input-label for="email" :value="__('Login')" />
                    <x-text-input wire:model="form.email" id="email" class="block mt-2 w-full rounded-lg" type="email" name="email" required autofocus autocomplete="username" placeholder="seu@email.com" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Senha')" />
                    <x-text-input wire:model="form.password" id="password" class="block mt-2 w-full rounded-lg" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <input wire:model="form.remember" id="remember" type="hidden" name="remember" value="1">

                <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors shadow-sm">
                    {{ __('Entrar') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Esqueceu sua senha? (abaixo do card) -->
    @if (Route::has('password.request'))
        <p class="mt-6 text-center">
            <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded">
                {{ __('Esqueceu sua senha?') }}
            </a>
        </p>
    @endif
</div>
