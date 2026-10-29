<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Area */
class AreaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city_id' => $this->city_id,
            'city_name' => $this->city?->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
