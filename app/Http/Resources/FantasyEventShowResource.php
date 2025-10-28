<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyEventShowResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'play_date' => $this->play_date,
            'base_fee' => $this->base_fee,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'is_registered' => $this->isRegistered(),
            'teams' => $this->teams->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slot_limit' => $team->slot_limit,
                ];
            }),
        ];
    }
}