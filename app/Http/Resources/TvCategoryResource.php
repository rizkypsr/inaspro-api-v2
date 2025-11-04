<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TvCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tvs' => TvResource::collection($this->tvs ?? collect()),
        ];
    }
}