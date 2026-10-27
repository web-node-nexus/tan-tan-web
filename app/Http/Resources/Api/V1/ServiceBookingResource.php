<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ServiceBooking */
class ServiceBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_on' => $this->visit_on?->toDateString(),
            'slot' => $this->slot,
            'note' => $this->note,
            'details' => $this->details ?: null,
            'quoted_amount' => $this->quoted_amount,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'customer' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'listing' => $this->whenLoaded('listing', fn () => [
                'id' => $this->listing->id,
                'title' => $this->listing->title,
            ]),
        ];
    }
}
