<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->resource['items']->map(function ($item) {
                return [
                    'name' => $item['product_name'] . ($item['variant_name'] ? ' - ' . $item['variant_name'] : ''),
                    'price' => $item['total'],
                ];
            })->push([
                'name' => 'Shipping',
                'price' => $this->resource['shipping']['cost'],
            ]),
            'discounts' => $this->formatDiscounts(),
            'subtotal' => $this->resource['subtotal'],
            'total' => $this->resource['total_amount'],
        ];
    }

    /**
     * Format discounts for simplified response
     */
    private function formatDiscounts(): array
    {
        $discounts = [];
        
        // Add global voucher discounts
        if (isset($this->resource['vouchers']['global_vouchers'])) {
            foreach ($this->resource['vouchers']['global_vouchers'] as $voucher) {
                $discounts[] = [
                    'name' => $voucher['name'],
                    'price' => -$voucher['discount_amount'], // Negative to show as discount
                ];
            }
        }
        
        // Add product voucher discounts
        if (isset($this->resource['vouchers']['product_vouchers'])) {
            foreach ($this->resource['vouchers']['product_vouchers'] as $voucher) {
                $discounts[] = [
                    'name' => $voucher['name'] . ' (' . $voucher['product_name'] . ')',
                    'price' => -$voucher['discount_amount'], // Negative to show as discount
                ];
            }
        }
        
        return $discounts;
    }
}