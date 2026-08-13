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

    public bool $following = false;

    public int $recommendationsCount = 0;

    public int $notRecommendationsCount = 0;

    public ?string $followMessage = null;

    public function mount(): void
    {
        $this->refreshState();
    }

    private function refreshState(): void
    {
        /*
         * Current user vote.
         */
        $this->currentVote = Vote::query()
            ->where('user_id', Auth::id())
            ->where('publication_id', $this->publication->id)
            ->value('vote');

        /*
         * Follow of the current user.
         */
        $this->following = Follow::query()
            ->where('user_id', Auth::id())
            ->where('publication_id', $this->publication->id)
            ->exists();

        /*
         * Recommendation counter.
         */
        $this->recommendationsCount = Vote::query()
            ->where('publication_id', $this->publication->id)
            ->where('vote', VoteType::RECOMMEND->value)
            ->count();

        /*
         * Not recommendation counter.
         */
        $this->notRecommendationsCount = Vote::query()
            ->where('publication_id', $this->publication->id)
            ->where('vote', VoteType::NOT_RECOMMEND->value)
            ->count();
    }

    public function vote(VoteType $vote): void
    {
        abort_unless(Auth::check(), 403);

        if ($this->publication->status !== PublicationStatus::OPEN) {
            return;
        }

        $this->followMessage = null;

        DB::transaction(function () use ($vote) {

            $currentVote = Vote::query()
                ->where('user_id', Auth::id())
                ->where('publication_id', $this->publication->id)
                ->first();

            /*
             * Clicked on the same vote again
             * Remove:
             * - voto
             */
            if ($currentVote?->vote === $vote) {

                $currentVote->delete();

                // Follow::query()
                //     ->where('user_id', Auth::id())
                //     ->where('publication_id', $this->publication->id)
                //     ->delete();

                return;
            }

            /*
             * Create or change the vote.
             */
            Vote::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'publication_id' => $this->publication->id,
                ],
                [
                    'vote' => $vote->value,
                ]
            );

            /*
             * Whoever votes automatically follows
             */
            Follow::firstOrCreate([
                'user_id' => Auth::id(),
                'publication_id' => $this->publication->id,
            ]);
        });

        $this->refreshState();
    }

    public function toggleFollow(): void
    {
        abort_unless(Auth::check(), 403);

        $this->followMessage = null;

        /*
         * If there is a vote, the user is obliged to continue following
         */
        if ($this->currentVote !== null) {

            $this->following = true;

            $this->followMessage =
                'You can\'t unfollow as long as you have a vote on this post.';

            return;
        }

        /*
         * Search for the current follow
         */
        $follow = Follow::query()
            ->where('user_id', Auth::id())
            ->where('publication_id', $this->publication->id)
            ->first();

        /*
         * Already follow and don't have a vote:
         * you can unfollow
         */
        if ($follow) {

            $follow->delete();

            $this->following = false;
        } else {

            /*
             * Not following:
             * start following.
             */
            Follow::create([
                'user_id' => Auth::id(),
                'publication_id' => $this->publication->id,
            ]);

            $this->following = true;
        }

        $this->refreshState();
    }

    public function deletePublication(): void
    {
        abort_unless(
            Auth::id() === $this->publication->user_id,
            403
        );

        if (
            $this->publication->votes()->exists() ||
            $this->publication->follows()->exists()
        ) {
            $this->followMessage =
                'This post cannot be deleted because it has votes or followers.';

            return;
        }

        $this->publication->delete();

        $this->redirectRoute('feed');
    }
};
?>

<div>
    <article class="rounded-xl bg-white p-6 shadow-sm">

        {{-- Header --}}
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

        {{-- Description --}}
        <p class="mt-4 text-gray-700">
            {{ $publication->description }}
        </p>

        {{-- Actions --}}
        <div class="mt-6 flex flex-wrap gap-3">

            {{-- I recommend --}}
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

            {{-- I don't recommend --}}
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

            {{-- Follow --}}
            <button
                type="button"
                wire:click="toggleFollow"
                wire:loading.attr="disabled"
                class="rounded-lg border px-4 py-2 text-sm font-medium transition
                    {{ $following
                        ? ($currentVote
                            ? 'border-blue-500 bg-blue-100 text-blue-700'
                            : 'border-gray-400 bg-gray-100 text-gray-700')
                        : 'border-gray-300 hover:bg-gray-50' }}">

                @if ($following)

                @if ($currentVote)
                ✓ Following · active vote
                @else
                ✓ Following
                @endif

                @else

                + Follow

                @endif

            </button>

            @if (Auth::id() === $publication->user_id)
            <button
                type="button"
                wire:click="deletePublication"
                wire:confirm="Are you sure you want to delete this post?"
                wire:loading.attr="disabled"
                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                🗑️ Delete
            </button>
            @endif

        </div>

        {{-- Message about Follow --}}
        @if ($followMessage)
        <div class="mt-3 rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            🔒 {{ $followMessage }}
        </div>
        @endif

        {{-- Accountants --}}
        <div class="mt-4 flex gap-6 text-sm text-gray-600">

            <span>
                👍 {{ $recommendationsCount }}
            </span>

            <span>
                👎 {{ $notRecommendationsCount }}
            </span>

        </div>

    </article>
</div>