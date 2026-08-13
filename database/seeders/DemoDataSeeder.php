<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Enums\VoteType;
use App\Models\Follow;
use App\Models\Publication;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $gabriel = User::factory()->create([
            'name' => 'Gabriel',
            'email' => 'gabriel@example.com',
        ]);

        $ana = User::factory()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
        ]);

        $bruno = User::factory()->create([
            'name' => 'Bruno',
            'email' => 'bruno@example.com',
        ]);

        $openPublication = Publication::create([
            'user_id' => $gabriel->id,
            'title' => 'Interestelar',
            'type' => PublicationType::MOVIE,
            'description' => 'Estou pensando em assistir neste fim de semana. Vale a pena?',
            'status' => PublicationStatus::OPEN,
        ]);

        $closedPublication = Publication::create([
            'user_id' => $ana->id,
            'title' => 'Ruptura',
            'type' => PublicationType::SERIES,
            'description' => 'Quero uma série de suspense para começar. Recomendam?',
            'status' => PublicationStatus::CLOSED,
        ]);

        Vote::create([
            'user_id' => $ana->id,
            'publication_id' => $openPublication->id,
            'vote' => VoteType::RECOMMEND,
        ]);

        Vote::create([
            'user_id' => $bruno->id,
            'publication_id' => $openPublication->id,
            'vote' => VoteType::NOT_RECOMMEND,
        ]);

        Vote::create([
            'user_id' => $gabriel->id,
            'publication_id' => $closedPublication->id,
            'vote' => VoteType::RECOMMEND,
        ]);

        Follow::insert([
            [
                'user_id' => $ana->id,
                'publication_id' => $openPublication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $bruno->id,
                'publication_id' => $openPublication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $gabriel->id,
                'publication_id' => $closedPublication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $bruno->id,
                'publication_id' => $closedPublication->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
