<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sports marketplace catalog...');

        // Create specific sports categories
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

        // Create categories
        $createdCategories = [];
        foreach ($sportsCategories as $categoryData) {
            $createdCategories[] = Category::create($categoryData);
        }

        $this->command->info('Created ' . count($createdCategories) . ' sports categories');

        // Create products for each category using factories
        $totalProducts = 0;
        $totalVariants = 0;

        foreach ($createdCategories as $category) {
            // Create 3-5 products per category
            $productCount = rand(3, 5);
            
            for ($i = 0; $i < $productCount; $i++) {
                $product = Product::factory()->create([
                    'category_id' => $category->id,
                    'status' => 'active'
                ]);

                // Create 2-4 variants per product
                $variantCount = rand(2, 4);
                
                for ($j = 0; $j < $variantCount; $j++) {
                    ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'status' => 'active'
                    ]);
                    $totalVariants++;
                }
                
                $totalProducts++;
            }
        }

        $this->command->info('Sports marketplace catalog seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($createdCategories) . ' sports categories');
        $this->command->info('- ' . $totalProducts . ' sports products');
        $this->command->info('- ' . $totalVariants . ' product variants with real images');
        $this->command->info('All products are now sports-related with real image URLs from Unsplash!');
    }
}
