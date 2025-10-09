<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\ShippingRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all provinces
        $provinces = Province::all();
        
        // Common couriers in Indonesia
        $couriers = [
            'JNE' => [
                'REG' => ['min' => 8000, 'max' => 25000],
                'OKE' => ['min' => 6000, 'max' => 20000],
                'YES' => ['min' => 12000, 'max' => 35000],
            ],
            'J&T' => [
                'REG' => ['min' => 7000, 'max' => 22000],
                'EZ' => ['min' => 5000, 'max' => 18000],
            ],
            'SiCepat' => [
                'REG' => ['min' => 7500, 'max' => 23000],
                'BEST' => ['min' => 9000, 'max' => 28000],
            ],
            'TIKI' => [
                'REG' => ['min' => 8500, 'max' => 26000],
                'ONS' => ['min' => 11000, 'max' => 32000],
            ],
            'POS Indonesia' => [
                'Paket Kilat Khusus' => ['min' => 9000, 'max' => 27000],
                'Express Next Day' => ['min' => 15000, 'max' => 45000],
            ],
            'AnterAja' => [
                'REG' => ['min' => 6500, 'max' => 21000],
                'SAME DAY' => ['min' => 20000, 'max' => 50000],
            ],
        ];

        foreach ($provinces as $province) {
            foreach ($couriers as $courierName => $services) {
                foreach ($services as $serviceName => $priceRange) {
                    // Calculate rate based on distance from Jakarta (simplified logic)
                    $baseRate = rand($priceRange['min'], $priceRange['max']);
                    
                    // Adjust rates based on province location
                    $rate = $this->adjustRateByProvince($province->name, $baseRate);
                    
                    ShippingRate::create([
                        'province_id' => $province->id,
                        'courier' => $courierName . ' - ' . $serviceName,
                        'rate' => $rate,
                    ]);
                }
            }
        }
    }

    /**
     * Adjust shipping rate based on province location
     */
    private function adjustRateByProvince(string $provinceName, int $baseRate): int
    {
        // Jakarta and surrounding areas (cheaper)
        $jakartaArea = ['DKI Jakarta', 'Jawa Barat', 'Banten'];
        
        // Java island (moderate)
        $javaArea = ['Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur'];
        
        // Sumatera (moderate to expensive)
        $sumateraArea = ['Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Sumatera Selatan', 'Bangka Belitung', 'Bengkulu', 'Lampung'];
        
        // Eastern Indonesia (more expensive)
        $easternArea = ['Papua', 'Papua Barat', 'Papua Tengah', 'Papua Pegunungan', 'Papua Selatan', 'Papua Barat Daya', 'Maluku', 'Maluku Utara'];
        
        if (in_array($provinceName, $jakartaArea)) {
            return (int) ($baseRate * 0.8); // 20% cheaper
        } elseif (in_array($provinceName, $javaArea)) {
            return $baseRate; // Base rate
        } elseif (in_array($provinceName, $sumateraArea)) {
            return (int) ($baseRate * 1.2); // 20% more expensive
        } elseif (in_array($provinceName, $easternArea)) {
            return (int) ($baseRate * 1.8); // 80% more expensive
        } else {
            return (int) ($baseRate * 1.4); // 40% more expensive for other areas
        }
    }
}
