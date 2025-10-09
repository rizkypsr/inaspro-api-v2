<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Real sports product images from Unsplash
        $sportsImages = [
            // Football Equipment
            'https://images.unsplash.com/photo-1560272564-c83b66b1ad12?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1566577739112-5180d4bf9390?w=500&h=500&fit=crop',
            
            // Basketball
            'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1574623452334-1e0ac2b3ccb4?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop',
            
            // Running & Athletics
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop',
            
            // Fitness & Gym
            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1506629905607-d9c297d3d45f?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&h=500&fit=crop',
            
            // Tennis & Racquet Sports
            'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1530549387789-4c1017266635?w=500&h=500&fit=crop',
            
            // Swimming & Water Sports
            'https://images.unsplash.com/photo-1530549387789-4c1017266635?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=500&fit=crop',
            
            // Cycling
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1544191696-15693072b5a8?w=500&h=500&fit=crop',
            
            // Team Sports
            'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop',
            'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=500&h=500&fit=crop',
        ];

        $colors = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Orange', 'Purple', 'Gray', 'Navy'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '6', '7', '8', '9', '10', '11', '12'];
        
        $color = $this->faker->randomElement($colors);
        $size = $this->faker->randomElement($sizes);
        $variantName = $this->faker->boolean(70) ? "$size $color" : $color;
        
        // Generate SKU based on variant name
        $sku = strtoupper(substr(str_replace(' ', '', $variantName), 0, 3)) . '-' . 
               $this->faker->randomNumber(3) . '-' . 
               strtoupper(substr($color, 0, 3));

        return [
            'product_id' => Product::factory(),
            'sku' => $sku,
            'variant_name' => $variantName,
            'image_url' => $this->faker->randomElement($sportsImages),
            'price' => $this->faker->numberBetween(1000, 50000), // Price in cents
            'stock' => $this->faker->numberBetween(0, 200),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
