<?php

use App\Models\Publication;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $tab = 'all';

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'following'], true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function getPublicationsProperty()
    {
        $query = Publication::query()
            ->with('user')
            ->withCount([
                'votes as recommendations_count' => function ($query) {
                    $query->where('vote', 'recommend');
                },
                'votes as not_recommendations_count' => function ($query) {
                    $query->where('vote', 'not_recommend');
                },
            ])
            ->latest();

        if ($this->tab === 'all') {
            $query->where('status', 'open');
        }

        if ($this->tab === 'following') {
            $query->whereHas('follows', function ($query) {
                $query->where('user_id', Auth::id());
            });
        }

        return $query->get();
    }
};
?>

<div class="min-h-screen bg-gray-100">

    <div class="mx-auto max-w-7xl px-4 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Watch Cine Opina
                </h1>

                <p class="mt-1 text-gray-600">
                    See what the community recommends
                </p>
            </div>

            <div class="flex items-center gap-3">

                {{-- New post --}}
                <livewire:publication.create />

                {{-- User --}}
                <span class="text-sm font-medium text-gray-700">
                    Hello, {{ auth()->user()->name }}
                </span>

                {{-- Logout --}}
                <a
                    href="{{ url('/logout') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Logout
                </a>

            </div>

        </div>

        {{-- Tabs --}}
        <div class="mt-8 border-b border-gray-200">

            <div class="flex gap-8">

                <button
                    type="button"
                    wire:click="setTab('all')"
                    class="border-b-2 px-1 pb-3 text-sm font-medium transition
                        {{ $tab === 'all'
                            ? 'border-black text-gray-900'
                            : 'border-transparent text-gray-500 hover:text-gray-900' }}">
                    All
                </button>

                <button
                    type="button"
                    wire:click="setTab('following')"
                    class="border-b-2 px-1 pb-3 text-sm font-medium transition
                        {{ $tab === 'following'
                            ? 'border-black text-gray-900'
                            : 'border-transparent text-gray-500 hover:text-gray-900' }}">
                    Following
                </button>

            </div>

        </div>

        {{-- Feed --}}
        <div class="mt-6 space-y-4">

            @forelse ($this->publications as $publication)

            <livewire:publication.card
                :publication="$publication"
                :key="$publication->id . '-' . $tab" />

            @empty

            <div class="rounded-xl bg-white p-8 text-center shadow-sm">

                @if ($tab === 'all')

                <p class="text-gray-500">
                    There are no open publications yet
                </p>

                @else

                <p class="text-gray-500">
                    You don't follow any posts yet
                </p>

                @endif

            </div>

            @endforelse

        </div>

    </div>

</div>