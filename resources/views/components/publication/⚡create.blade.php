<?php

use App\Enums\PublicationType;
use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{

    public string $title = '';

    public string $type = '';

    public string $description = '';

    public function createPublication(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:movie,series'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        Publication::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'description' => $validated['description'],
        ]);

        $this->reset([
            'title',
            'type',
            'description',
        ]);

        $this->dispatch('publication-created');
    }

};
?>

<div>
    <button
        type="button"
        x-data
        @click="$dispatch('open-publication-modal')"
        class="rounded-lg bg-black px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800"
    >
        + New publication
    </button>

    <div
        x-data="{ open: false }"
        @open-publication-modal.window="open = true"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div
            class="absolute inset-0 bg-black/50"
            @click="open = false"
        ></div>

        <div
            class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl"
            @click.stop
        >
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">
                    New publication
                </h2>

                <button
                    type="button"
                    @click="open = false"
                    class="text-2xl text-gray-400 hover:text-gray-600"
                >
                    &times;
                </button>
            </div>

            <form wire:submit="createPublication" class="mt-6 space-y-4">

                <div>
                    <label
                        for="title"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Film or series
                    </label>

                    <input
                        id="title"
                        type="text"
                        wire:model="title"
                        class="mt-1 block w-full rounded-lg border-gray-300"
                        placeholder="Ex.: Interestelar"
                    >

                    @error('title')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="type"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Type
                    </label>

                    <select
                        id="type"
                        wire:model="type"
                        class="mt-1 block w-full rounded-lg border-gray-300"
                    >
                        <option value="">Select</option>
                        <option value="movie">Film</option>
                        <option value="series">Series</option>
                    </select>

                    @error('type')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="description"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Comment
                    </label>

                    <textarea
                        id="description"
                        wire:model="description"
                        rows="4"
                        class="mt-1 block w-full rounded-lg border-gray-300"
                        placeholder="O que você está pensando em assistir?"
                    ></textarea>

                    @error('description')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-black px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800"
                    >
                        Post
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>