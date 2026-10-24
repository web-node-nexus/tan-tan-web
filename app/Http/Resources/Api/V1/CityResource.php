<?php

namespace App\Http\Resources\Api\V1;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin City */
class CityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'state_id' => $this->state_id,
            'state' => $this->whenLoaded('state', fn () => $this->state?->name),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
