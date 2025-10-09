<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sportsCategories = [
            [
                'name' => 'Football Equipment',
                'description' => 'Professional and amateur football gear including balls, cleats, and protective equipment'
            ],
            [
                'name' => 'Basketball Gear',
                'description' => 'Basketball equipment including balls, shoes, hoops, and training accessories'
            ],
            [
                'name' => 'Running & Athletics',
                'description' => 'Running shoes, athletic wear, and track & field equipment'
            ],
            [
                'name' => 'Fitness & Gym',
                'description' => 'Home gym equipment, weights, and fitness accessories'
            ],
            [
                'name' => 'Tennis & Racquet Sports',
                'description' => 'Tennis rackets, balls, and court equipment for all racquet sports'
            ],
            [
                'name' => 'Swimming & Water Sports',
                'description' => 'Swimming gear, water sports equipment, and pool accessories'
            ],
            [
                'name' => 'Cycling',
                'description' => 'Bicycles, cycling gear, and bike accessories'
            ],
            [
                'name' => 'Team Sports',
                'description' => 'Equipment for various team sports including soccer, volleyball, and baseball'
            ]
        ];

        $category = $this->faker->randomElement($sportsCategories);
        
        return [
            'name' => $category['name'],
            'description' => $category['description'],
        ];
    }
}
