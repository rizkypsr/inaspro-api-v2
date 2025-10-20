<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'cart_id' => $this->cart_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_proof' => $this->payment_proof,
            'total_amount' => $this->total_amount,
            'shipping_address' => $this->shipping_address,
            'courier_name' => $this->courier_name,
            "tracking_number" => $this->tracking_number,
            'items' => $this->orderItems->map(function ($item) {
                return [
                    'variant_name' => $item->productVariant->variant_name,
                    'variant_quantity' => $item->quantity,
                    'variant_price' => $item->price,
                    'variant_image_url' => $item->productVariant->image_url,
                    'product_name' => $item->productVariant->product->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
