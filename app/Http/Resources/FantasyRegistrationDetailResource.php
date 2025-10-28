<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyRegistrationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate summary
        $baseFee = $this->registration_fee ?? 0;
        $shoesFee = $this->registrationItems->sum(function ($item) {
            return $item->fantasyShoe ? $item->fantasyShoe->price : 0;
        });
        $totalAmount = $baseFee + $shoesFee;

        return [
            'id' => $this->id,
            'registration_code' => $this->registration_code,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'event_title' => $this->fantasyEvent->title,
            'event_location' => $this->fantasyEvent->location,
            'team_name' => $this->fantasyEventTeam->name,
            'registration_items' => $this->registrationItems->map(function ($item) {
                if ($item->isTshirt()) {
                    return [
                        'type' => 'tshirt',
                        'size' => $item->fantasyTshirtOption->size ?? null,
                        'price' => $item->price,
                    ];
                } elseif ($item->isShoe()) {
                    return [
                        'type' => 'shoe',
                        'size' => $item->fantasyShoeSize->size ?? null,
                        'price' => $item->price,
                    ];
                }
                return null;
            })->filter(),
            'summary' => array_filter([
                'subtotal' => $baseFee,
                'shoes_fee' => $shoesFee > 0 ? $shoesFee : null,
                'total_amount' => $totalAmount,
            ], function($value) {
                return $value !== null;
            }),
        ];
    }
}