<?php

use App\Enums\PublicationStatus;
use App\Enums\VoteType;
use App\Models\Follow;
use App\Models\Publication;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public Publication $publication;

    public ?VoteType $currentVote = null;

    public function mount(): void
    {
        $this->loadCurrentVote();
    }

    private function loadCurrentVote(): void
    {
        $this->currentVote = Vote::query()
            ->where('user_id', Auth::id())
            ->where('publication_id', $this->publication->id)
            ->value('vote');
    }

    public function vote(VoteType $vote): void
    {
        abort_unless(Auth::check(), 403);

        if ($this->publication->status !== PublicationStatus::OPEN) {
            return;
        }

        DB::transaction(function () use ($vote) {
            $currentVote = Vote::query()
                ->where('user_id', Auth::id())
                ->where('publication_id', $this->publication->id)
                ->first();

            // Clicked on the same vote: remove vote + follow
            if ($currentVote?->vote === $vote) {
                $currentVote->delete();

                Follow::query()
                    ->where('user_id', Auth::id())
                    ->where('publication_id', $this->publication->id)
                    ->delete();

                $this->currentVote = null;

                return;
            }

            // Create or change the vote
            Vote::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'publication_id' => $this->publication->id,
                ],
                [
                    'vote' => $vote->value,
                ]
            );

            // Votar significa acompanhar
            Follow::firstOrCreate([
                'user_id' => Auth::id(),
                'publication_id' => $this->publication->id,
            ]);

            $this->currentVote = $vote;
        });

        $this->publication->loadCount([
            'votes as recommendations_count' => fn($query) =>
            $query->where('vote', VoteType::RECOMMEND->value),

            'votes as not_recommendations_count' => fn($query) =>
            $query->where('vote', VoteType::NOT_RECOMMEND->value),
        ]);
    }
};
?>

<div>
    <article class="rounded-xl bg-white p-6 shadow-sm">

        <div class="flex items-start justify-between">

            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ $publication->title }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    por {{ $publication->user->name }}
                </p>
            </div>

            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium">
                {{ $publication->type->value === 'movie' ? 'Movie' : 'Series' }}
            </span>

        </div>

        <p class="mt-4 text-gray-700">
            {{ $publication->description }}
        </p>

        <div class="mt-6 flex gap-3">

            <div class="mt-6 flex gap-3">

                <button
                    type="button"
                    wire:click="vote('recommend')"
                    wire:loading.attr="disabled"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition
            {{ $currentVote?->value === 'recommend'
                ? 'border-green-500 bg-green-100 text-green-700'
                : 'border-gray-300 hover:bg-gray-50' }}">
                    👍 I recommend

                    @if ($currentVote?->value === 'recommend')
                    ✓
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="vote('not_recommend')"
                    wire:loading.attr="disabled"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition
            {{ $currentVote?->value === 'not_recommend'
                ? 'border-red-500 bg-red-100 text-red-700'
                : 'border-gray-300 hover:bg-gray-50' }}">
                    👎 I don't recommend

                    @if ($currentVote?->value === 'not_recommend')
                    ✓
                    @endif
                </button>

            </div>

        </div>

        <div class="mt-4 flex gap-6 text-sm text-gray-600">
            <span>
                👍 {{ $publication->recommendations_count }}
            </span>

            <span>
                👎 {{ $publication->not_recommendations_count }}
            </span>
        </div>

    </article>
</div>