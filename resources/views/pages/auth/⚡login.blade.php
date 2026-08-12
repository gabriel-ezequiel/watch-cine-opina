<?php

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated, $this->remember)) {
            $this->addError('email', 'The provided credentials do not match our records.');
            return;
        }

        session()->regenerate();

        $this->redirect('/feed');

    }
};
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login</h2>

        <form wire:submit="login" class="space-y-4">
            <!-- E-mail -->
            <div>
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" wire:model="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Remember me -->
            <div class="flex items-center">
                <input type="checkbox" wire:model="remember" id="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Remember me on this device</label>
            </div>

            <!-- Login button with native loading -->
            <div>
                <button type="submit" wire:loading.attr="disabled" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    <span wire:loading.remove>Login</span>
                    <span wire:loading>Validating data...</span>
                </button>
            </div>
        </form>

        <!-- Link to registration -->
        <p class="mt-4 text-center text-sm text-gray-600">
            Don't have an account yet? 
            <a href="/register" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500">Register here</a>
        </p>
    </div>
</div>
