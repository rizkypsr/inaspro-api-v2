<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sportsProducts = [
            // Football Equipment
            ['name' => 'Professional Football', 'description' => 'Official size and weight football for professional games', 'price_range' => [25, 80]],
            ['name' => 'Football Cleats', 'description' => 'High-performance football cleats with superior grip', 'price_range' => [80, 250]],
            ['name' => 'Football Helmet', 'description' => 'Safety-certified football helmet with face guard', 'price_range' => [150, 400]],
            ['name' => 'Shoulder Pads', 'description' => 'Protective shoulder pads for football players', 'price_range' => [100, 300]],
            
            // Basketball Gear
            ['name' => 'Basketball', 'description' => 'Official size basketball for indoor and outdoor play', 'price_range' => [20, 60]],
            ['name' => 'Basketball Shoes', 'description' => 'High-top basketball shoes with ankle support', 'price_range' => [90, 300]],
            ['name' => 'Basketball Hoop', 'description' => 'Adjustable height basketball hoop system', 'price_range' => [200, 800]],
            ['name' => 'Basketball Jersey', 'description' => 'Breathable basketball jersey with moisture-wicking fabric', 'price_range' => [30, 80]],
            
            // Running & Athletics
            ['name' => 'Running Shoes', 'description' => 'Lightweight running shoes with advanced cushioning', 'price_range' => [80, 250]],
            ['name' => 'Athletic Shorts', 'description' => 'Comfortable athletic shorts for running and training', 'price_range' => [25, 60]],
            ['name' => 'Running Watch', 'description' => 'GPS-enabled running watch with heart rate monitor', 'price_range' => [150, 500]],
            ['name' => 'Track Spikes', 'description' => 'Professional track and field spikes for competitive running', 'price_range' => [100, 300]],
            
            // Fitness & Gym
            ['name' => 'Adjustable Dumbbells', 'description' => 'Space-saving adjustable dumbbells for home workouts', 'price_range' => [200, 600]],
            ['name' => 'Yoga Mat', 'description' => 'Non-slip yoga mat with extra cushioning', 'price_range' => [20, 80]],
            ['name' => 'Resistance Bands', 'description' => 'Set of resistance bands for strength training', 'price_range' => [15, 50]],
            ['name' => 'Kettlebell', 'description' => 'Cast iron kettlebell for functional fitness training', 'price_range' => [30, 100]],
            
            // Tennis & Racquet Sports
            ['name' => 'Tennis Racket', 'description' => 'Professional tennis racket with graphite frame', 'price_range' => [80, 300]],
            ['name' => 'Tennis Balls', 'description' => 'High-quality tennis balls for tournament play', 'price_range' => [5, 20]],
            ['name' => 'Badminton Set', 'description' => 'Complete badminton set with rackets and shuttlecocks', 'price_range' => [40, 120]],
            ['name' => 'Table Tennis Paddle', 'description' => 'Professional table tennis paddle with rubber surface', 'price_range' => [25, 100]],
            
            // Swimming & Water Sports
            ['name' => 'Swimming Goggles', 'description' => 'Anti-fog swimming goggles with UV protection', 'price_range' => [15, 50]],
            ['name' => 'Swimsuit', 'description' => 'Competitive swimsuit with hydrodynamic design', 'price_range' => [40, 150]],
            ['name' => 'Kickboard', 'description' => 'Swimming kickboard for training and technique improvement', 'price_range' => [10, 30]],
            ['name' => 'Water Polo Ball', 'description' => 'Official water polo ball with textured grip', 'price_range' => [20, 50]],
            
            // Cycling
            ['name' => 'Road Bike', 'description' => 'Lightweight road bike with carbon fiber frame', 'price_range' => [800, 3000]],
            ['name' => 'Cycling Helmet', 'description' => 'Aerodynamic cycling helmet with ventilation system', 'price_range' => [50, 200]],
            ['name' => 'Bike Lock', 'description' => 'Heavy-duty bike lock for security', 'price_range' => [25, 80]],
            ['name' => 'Cycling Shorts', 'description' => 'Padded cycling shorts for long-distance comfort', 'price_range' => [40, 120]],
            
            // Team Sports
            ['name' => 'Soccer Ball', 'description' => 'FIFA-approved soccer ball for professional matches', 'price_range' => [20, 80]],
            ['name' => 'Volleyball', 'description' => 'Official volleyball for indoor and beach play', 'price_range' => [25, 70]],
            ['name' => 'Baseball Glove', 'description' => 'Leather baseball glove with deep pocket', 'price_range' => [50, 200]],
            ['name' => 'Hockey Stick', 'description' => 'Composite hockey stick for ice hockey', 'price_range' => [80, 300]]
        ];

        $product = $this->faker->randomElement($sportsProducts);
        $basePrice = $this->faker->numberBetween($product['price_range'][0], $product['price_range'][1]);

        return [
            'category_id' => Category::factory(),
            'name' => $product['name'],
            'description' => $product['description'],
            'base_price' => $basePrice * 100, // Convert to cents
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
