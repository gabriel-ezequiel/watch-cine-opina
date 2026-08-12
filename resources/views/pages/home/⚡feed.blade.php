<?php

use App\Enums\VoteType;
use App\Models\Publication;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{

    #[On('publication-created')]
    public function refreshFeed(): void
    {
        //
    }

    public function getPublicationsProperty()
    {
        return Publication::query()
            ->with('user')
            ->withCount([
                'votes as recommendations_count' => fn($query) =>
                $query->where('vote', VoteType::RECOMMEND->value),

                'votes as not_recommendations_count' => fn($query) =>
                $query->where('vote', VoteType::NOT_RECOMMEND->value),
            ])
            ->where('status', 'open')
            ->latest()
            ->get();
    }
};
?>

<div class="min-h-screen bg-gray-100">
    <div class="mx-auto max-w-7xl px-4 py-8">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Watch Cine Opina
                </h1>

                <p class="mt-1 text-gray-600">
                    See what the community recommends.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">
                    Hello, {{ auth()->user()->name }}
                </span>

                <livewire:publication.create />
            </div>
        </div>

        <div class="mt-8 space-y-4">

            @forelse ($this->publications as $publication)

            <livewire:publication.card
                :publication="$publication"
                :key="$publication->id" />

            @empty

            <div class="rounded-xl bg-white p-8 text-center shadow-sm">
                <p class="text-gray-500">
                    There are no open publications yet.
                </p>
            </div>

            @endforelse

        </div>

    </div>
</div>