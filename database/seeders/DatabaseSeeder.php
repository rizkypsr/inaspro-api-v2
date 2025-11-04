<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed roles first
        $this->call([
            RoleSeeder::class,
        ]);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Seed admin user with role
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Seed product catalog data
        $this->call([
            ProductCatalogSeeder::class,
        ]);

        // Seed provinces and shipping rates
        $this->call([
            ProvinceSeeder::class,
            ShippingRateSeeder::class,
        ]);
    }
}
