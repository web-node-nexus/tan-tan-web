<?php

namespace App\Services\Trust;

use App\Models\Conversation;
use App\Models\JobApplication;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Offer;
use App\Models\ServiceBooking;
use App\Models\User;

final class ReviewEligibility
{
    public function allowed(User $reviewer, Listing $listing): bool
    {
        if ($reviewer->id === $listing->user_id) {
            return false;
        }

        if (Offer::query()->where('listing_id', $listing->id)->where('buyer_id', $reviewer->id)->where('status', 'accepted')->exists()) {
            return true;
        }

        if (JobApplication::query()->where('listing_id', $listing->id)->where('user_id', $reviewer->id)->where('status', 'hired')->exists()) {
            return true;
        }

        if (ServiceBooking::query()->where('listing_id', $listing->id)->where('user_id', $reviewer->id)->where('status', 'done')->exists()) {
            return true;
        }

        return $listing->status === 'sold' && $this->completedChat($reviewer, $listing);
    }

    private function completedChat(User $reviewer, Listing $listing): bool
    {
        $conversation = Conversation::query()
            ->where('listing_id', $listing->id)
            ->where('buyer_id', $reviewer->id)
            ->where('seller_id', $listing->user_id)
            ->first();

        if (! $conversation) {
            return false;
        }

        $senders = Message::query()->where('conversation_id', $conversation->id)->distinct()->pluck('sender_id');

        return $senders->contains($reviewer->id) && $senders->contains($listing->user_id);
    }
}
