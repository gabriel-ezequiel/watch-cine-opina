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

        /*
         * Finished publications cannot receive
         * new votes or changes to existing votes.
         */
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
             * Clicking the same vote again removes the vote.
             *
             * The follow is NOT removed.
             */
            if ($currentVote?->vote === $vote->value) {

                $currentVote->delete();

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
             * Anyone who votes automatically follows
             * the publication.
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
         * If there is a vote, the user must continue following.
         */
        if ($this->currentVote !== null) {

            $this->following = true;

            $this->followMessage =
                'You can\'t unfollow as long as you have a vote on this post.';

            return;
        }

        /*
         * Search for the current follow.
         */
        $follow = Follow::query()
            ->where('user_id', Auth::id())
            ->where('publication_id', $this->publication->id)
            ->first();

        /*
         * Finished publication.
         *
         * Existing followers must remain followers.
         * New followers are not allowed.
         */
        if ($this->publication->status !== PublicationStatus::OPEN) {

            if ($follow) {

                $this->following = true;

                $this->followMessage =
                    'This publication has been finished. You must continue following it.';
            } else {

                $this->following = false;

                $this->followMessage =
                    'This publication has been finished and does not accept new followers.';
            }

            return;
        }

        /*
         * Open publication + already following:
         * the user can unfollow.
         */
        if ($follow) {

            $follow->delete();

            $this->following = false;

            $this->refreshState();

            return;
        }

        /*
         * Open publication + not following:
         * start following.
         */
        Follow::create([
            'user_id' => Auth::id(),
            'publication_id' => $this->publication->id,
        ]);

        $this->following = true;

        $this->refreshState();
    }

    public function deletePublication(): void
    {
        abort_unless(
            Auth::id() === $this->publication->user_id,
            403
        );

        /*
         * The author cannot delete a publication
         * that already has any interaction.
         */
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

    public function finishPublication(): void
    {
        abort_unless(
            Auth::id() === $this->publication->user_id,
            403
        );

        /*
         * Only open publications can be finished.
         */
        if ($this->publication->status !== PublicationStatus::OPEN) {
            return;
        }

        $this->publication->update([
            'status' => PublicationStatus::CLOSED,
        ]);

        $this->publication->refresh();

        $this->refreshState();
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
                @disabled($publication->status !== \App\Enums\PublicationStatus::OPEN)
                class="rounded-lg border px-4 py-2 text-sm font-medium transition
                {{ $publication->status !== \App\Enums\PublicationStatus::OPEN
                        ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
                        : ($currentVote?->value === 'recommend'
                            ? 'border-green-500 bg-green-100 text-green-700'
                            : 'border-gray-300 hover:bg-gray-50') }}"
                >
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
                @disabled($publication->status !== \App\Enums\PublicationStatus::OPEN)
                class="rounded-lg border px-4 py-2 text-sm font-medium transition
                {{ $publication->status !== \App\Enums\PublicationStatus::OPEN
                        ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
                        : ($currentVote?->value === 'not_recommend'
                            ? 'border-red-500 bg-red-100 text-red-700'
                            : 'border-gray-300 hover:bg-gray-50') }}"
                >
                👎 I don't recommend

                @if ($currentVote?->value === 'not_recommend')
                ✓
                @endif
            </button>

            {{-- Follow --}}
            @if ($following)

            @if ($publication->status === \App\Enums\PublicationStatus::OPEN)

            <button
                type="button"
                wire:click="toggleFollow"
                wire:loading.attr="disabled"
                class="rounded-lg border px-4 py-2 text-sm font-medium transition
                            {{ $currentVote
                                ? 'border-blue-500 bg-blue-100 text-blue-700'
                                : 'border-gray-400 bg-gray-100 text-gray-700' }}">
                @if ($currentVote)
                ✓ Following · active vote
                @else
                ✓ Following
                @endif
            </button>

            @else

            <button
                type="button"
                disabled
                class="cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500">
                🔒 Following · finished
            </button>

            @endif

            @elseif ($publication->status === \App\Enums\PublicationStatus::OPEN)

            <button
                type="button"
                wire:click="toggleFollow"
                wire:loading.attr="disabled"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                + Follow
            </button>

            @else

            <button
                type="button"
                disabled
                class="cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
                🔒 Finished · Follow unavailable
            </button>

            @endif

            {{-- Finish --}}
            @if (
            Auth::id() === $publication->user_id &&
            $publication->status === \App\Enums\PublicationStatus::OPEN
            )

            <button
                type="button"
                wire:click="finishPublication"
                wire:confirm="Are you sure you want to finish this post? After that, votes can no longer be changed."
                wire:loading.attr="disabled"
                class="rounded-lg border border-orange-300 px-4 py-2 text-sm font-medium text-orange-600 hover:bg-orange-50">
                🔒 Finish
            </button>

            @endif

            {{-- Delete --}}
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

        {{-- Finished message --}}
        @if ($publication->status !== \App\Enums\PublicationStatus::OPEN)

        <div class="mt-4 rounded-lg bg-gray-100 px-4 py-3 text-sm text-gray-700">
            🔒 This publication has been finished.
        </div>

        @endif

        {{-- Follow message --}}
        @if ($followMessage)

        <div class="mt-3 rounded-lg bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            🔒 {{ $followMessage }}
        </div>

        @endif

        {{-- Vote counters --}}
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