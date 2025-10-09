<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            // Sumatera
            'Aceh',
            'Sumatera Utara',
            'Sumatera Barat',
            'Riau',
            'Kepulauan Riau',
            'Jambi',
            'Sumatera Selatan',
            'Bangka Belitung',
            'Bengkulu',
            'Lampung',
            
            // Jawa
            'DKI Jakarta',
            'Jawa Barat',
            'Banten',
            'Jawa Tengah',
            'DI Yogyakarta',
            'Jawa Timur',
            
            // Kalimantan
            'Kalimantan Barat',
            'Kalimantan Tengah',
            'Kalimantan Selatan',
            'Kalimantan Timur',
            'Kalimantan Utara',
            
            // Sulawesi
            'Sulawesi Utara',
            'Gorontalo',
            'Sulawesi Tengah',
            'Sulawesi Barat',
            'Sulawesi Selatan',
            'Sulawesi Tenggara',
            
            // Bali & Nusa Tenggara
            'Bali',
            'Nusa Tenggara Barat',
            'Nusa Tenggara Timur',
            
            // Maluku
            'Maluku',
            'Maluku Utara',
            
            // Papua
            'Papua',
            'Papua Barat',
            'Papua Tengah',
            'Papua Pegunungan',
            'Papua Selatan',
            'Papua Barat Daya',
        ];

        foreach ($provinces as $provinceName) {
            Province::create([
                'name' => $provinceName,
            ]);
        }
    }
}
