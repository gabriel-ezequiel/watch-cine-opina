<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        $this->redirect('/dashboard');
    }
};
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">
                    Criar conta
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Crie sua conta para começar a pedir opiniões.
                </p>
            </div>

            <form wire:submit="register" class="space-y-5">
                <div>
                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Nome
                    </label>

                    <input
                        id="name"
                        type="text"
                        wire:model="name"
                        autocomplete="name"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                    >
                        E-mail
                    </label>

                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    @error('email')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Senha
                    </label>

                    <input
                        id="password"
                        type="password"
                        wire:model="password"
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >

                    @error('password')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Confirmar senha
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        wire:model="password_confirmation"
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    Criar conta
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Já possui uma conta?
                <a
                    href="/login"
                    class="font-medium text-indigo-600 hover:text-indigo-500"
                >
                    Entrar
                </a>
            </p>
        </div>
    </div>
</div>