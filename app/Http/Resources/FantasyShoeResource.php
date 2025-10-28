<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyShoeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalStock = $this->sizes->sum('stock');
        $availableStock = $this->sizes->sum(function ($size) {
            return $size->stock - $size->reserved_stock;
        });

        return [
            'id' => $this->id,
            'fantasy_event_id' => $this->fantasy_event_id,
            'name' => $this->name,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'price' => $this->price,
            'sizes' => $this->sizes->map(function ($size) {
                return [
                    'id' => $size->id,
                    'size' => $size->size,
                    'stock' => $size->stock,
                    'available_stock' => $size->stock - $size->reserved_stock,
                ];
            }),
        ];
    }
}