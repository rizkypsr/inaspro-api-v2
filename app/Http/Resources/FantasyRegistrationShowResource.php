<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyRegistrationShowResource extends JsonResource
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
            'title' => $this->fantasyEvent->title ?? null,
            'description' => $this->fantasyEvent->description ?? null,
            'location' => $this->fantasyEvent->location ?? null,
            'play_date' => $this->fantasyEvent->play_date ?? null,
            'base_fee' => $this->fantasyEvent->base_fee ?? null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'teams' => $this->fantasyEvent->teams->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slot_limit' => $team->slot_limit,
                ];
            }),
        ];
    }
}