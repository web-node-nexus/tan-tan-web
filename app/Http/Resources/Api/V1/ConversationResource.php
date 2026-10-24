<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $other = $user ? $this->otherParty($user) : null;
        $last = $this->relationLoaded('messages') ? $this->messages->first() : null;
        $unread = (int) ($this->unread_count ?? 0);

        return [
            'id' => $this->id,
            'listing' => $this->whenLoaded('listing', fn () => [
                'id' => $this->listing->id,
                'title' => $this->listing->title,
                'price' => (float) $this->listing->price,
                'cover_url' => $this->listing->images->first()?->url(),
                'status' => $this->listing->status,
            ]),
            'other_user' => $other ? [
                'id' => $other->id,
                'name' => $other->name,
            ] : null,
            'last_message' => $last ? [
                'id' => $last->id,
                'body' => $last->body,
                'type' => $last->type,
                'created_at' => $last->created_at?->toIso8601String(),
                'is_mine' => $user?->id === $last->sender_id,
            ] : null,
            'unread_count' => $unread,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
        ];
    }
}
