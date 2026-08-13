<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'type' => PublicationType::MOVIE,
            'description' => fake()->paragraph(),
            'status' => PublicationStatus::OPEN,
        ];
    }
}