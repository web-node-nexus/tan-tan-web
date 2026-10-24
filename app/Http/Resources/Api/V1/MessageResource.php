<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'type' => $this->type,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->whenLoaded('sender', fn () => $this->sender?->name),
            'is_mine' => $request->user()?->id === $this->sender_id,
            'attachment' => $this->attachment_path ? [
                'url' => $this->attachment_url,
                'name' => $this->attachment_name,
                'mime' => $this->attachment_mime,
                'size' => $this->attachment_size,
                'is_image' => str_starts_with((string) $this->attachment_mime, 'image/'),
            ] : null,
            'offer' => $this->whenLoaded('offer', fn () => $this->offer ? [
                'id' => $this->offer->id,
                'amount' => (float) $this->offer->amount,
                'status' => $this->offer->status,
            ] : null),
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
