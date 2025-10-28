<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyTeamResource extends JsonResource
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
            'fantasy_event_id' => $this->fantasy_event_id,
            'name' => $this->name,
            'slot_limit' => $this->slot_limit,
            'registrations_count' => $this->registrations_count,
            'is_full' => ($this->slot_limit - $this->registrations_count) <= 0,
            'tshirt_options' => $this->tshirtOptions->map(function ($option) {
                return [
                    'id' => $option->id,
                    'fantasy_event_team_id' => $option->fantasy_event_team_id,
                    'size' => $option->size,
                ];
            }),
        ];
    }
}