<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Community>
 */
class CommunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'profile_image_url' => $this->faker->imageUrl(400, 400, 'community'),
            'is_private' => $this->faker->boolean(30), // 30% chance of being private
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
