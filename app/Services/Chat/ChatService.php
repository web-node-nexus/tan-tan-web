<?php

namespace App\Services\Chat;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Offer;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\Search\SavedSearchAlertService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ChatService
{
    public function start(User $buyer, Listing $listing, ?string $firstMessage = null): Conversation
    {
        if ($listing->status !== 'active') {
            throw ValidationException::withMessages(['listing' => 'This ad is not available for chat.']);
        }

        if ($listing->user_id === $buyer->id) {
            throw ValidationException::withMessages(['listing' => 'You cannot chat on your own ad.']);
        }

        $this->assertNotBlocked($buyer->id, $listing->user_id);

        $conversation = Conversation::query()->firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
            ],
            [
                'seller_id' => $listing->user_id,
                'last_message_at' => now(),
            ],
        );

        if ($conversation->wasRecentlyCreated) {
            $listing->increment('chats_count');
        }

        if ($firstMessage && trim($firstMessage) !== '') {
            $this->send($conversation, $buyer, trim($firstMessage));
        } elseif ($conversation->wasRecentlyCreated) {
            $this->send($conversation, $buyer, 'Hi! Is this still available?', 'system');
        }

        return $conversation->load([
            'listing.images',
            'listing.category',
            'buyer:id,name',
            'seller:id,name',
            'messages' => fn ($q) => $q->latest('id')->limit(1),
        ]);
    }

    public function forUser(User $user, int $perPage = 30): LengthAwarePaginator
    {
        return Conversation::query()
            ->with([
                'listing.images',
                'listing.category',
                'buyer:id,name',
                'seller:id,name',
                'messages' => fn ($q) => $q->latest('id')->limit(1),
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($user): void {
                    $q->whereNull('read_at')->where('sender_id', '!=', $user->id);
                },
            ])
            ->where(function ($q) use ($user): void {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            })
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    public function messages(Conversation $conversation, int $perPage = 50): LengthAwarePaginator
    {
        return $conversation->messages()
            ->with(['sender:id,name', 'offer'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function send(
        Conversation $conversation,
        User $sender,
        ?string $body = null,
        string $type = 'text',
        ?UploadedFile $attachment = null,
    ): Message {
        $this->assertParticipant($conversation, $sender);
        $otherId = $conversation->buyer_id === $sender->id
            ? $conversation->seller_id
            : $conversation->buyer_id;
        $this->assertNotBlocked($sender->id, $otherId);

        $trimmed = $body !== null ? trim($body) : '';
        $attachmentMeta = null;

        if ($attachment) {
            $path = $attachment->store('chat/'.$conversation->id, 'public');
            $attachmentMeta = [
                'attachment_path' => $path,
                'attachment_name' => $attachment->getClientOriginalName(),
                'attachment_mime' => $attachment->getClientMimeType() ?: $attachment->getMimeType(),
                'attachment_size' => $attachment->getSize(),
            ];
            $mime = (string) ($attachmentMeta['attachment_mime'] ?? '');
            if ($type === 'text') {
                $type = str_starts_with($mime, 'image/') ? 'image' : 'file';
            }
            if ($trimmed === '') {
                $trimmed = $type === 'image' ? '📷 Photo' : ('📎 '.$attachmentMeta['attachment_name']);
            }
        }

        if ($trimmed === '' && ! $attachmentMeta) {
            throw ValidationException::withMessages(['body' => 'Message cannot be empty.']);
        }

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => $trimmed,
            'type' => $type,
            ...($attachmentMeta ?? []),
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();
        $message->load('sender:id,name');

        broadcast(new MessageSent($message));

        return $message;
    }

    public function typing(Conversation $conversation, User $user): void
    {
        $this->assertParticipant($conversation, $user);
        broadcast(new UserTyping($conversation, $user))->toOthers();
    }

    public function makeOffer(Conversation $conversation, User $buyer, float $amount): Offer
    {
        $this->assertParticipant($conversation, $buyer);

        if ($conversation->buyer_id !== $buyer->id) {
            throw ValidationException::withMessages(['offer' => 'Only the buyer can make an offer.']);
        }

        $listing = $conversation->listing()->with('category')->first();
        if (! $listing || $listing->status !== 'active' || $listing->category?->kind !== 'product' || ! $listing->is_negotiable || $listing->reserved_at !== null) {
            throw ValidationException::withMessages(['offer' => 'Offers are open only on an active negotiable product that is not reserved.']);
        }
        if ($amount > (float) $listing->price) {
            throw ValidationException::withMessages(['amount' => 'Offer cannot be higher than the asking price.']);
        }

        return DB::transaction(function () use ($conversation, $buyer, $amount): Offer {
            $message = $this->send(
                $conversation,
                $buyer,
                'Offer: ₹'.number_format($amount, 0),
                'offer',
            );

            return Offer::query()->create([
                'conversation_id' => $conversation->id,
                'listing_id' => $conversation->listing_id,
                'buyer_id' => $buyer->id,
                'amount' => $amount,
                'status' => 'pending',
                'message_id' => $message->id,
            ]);
        });
    }

    public function respondOffer(Offer $offer, User $seller, string $status): Offer
    {
        if (! in_array($status, ['accepted', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid offer status.']);
        }

        $conversation = $offer->conversation;
        if ($conversation->seller_id !== $seller->id) {
            throw ValidationException::withMessages(['offer' => 'Only the seller can respond.']);
        }

        $offer->forceFill(['status' => $status])->save();

        if ($status === 'accepted') {
            $listing = $offer->listing;
            if ($listing && $listing->status === 'active') {
                $from = (float) $listing->price;
                $to = (float) $offer->amount;
                if ($to < $from) {
                    $listing->previous_price = $listing->price;
                }
                $listing->price = $offer->amount;
                $listing->reserved_at = now();
                $listing->save();
                if ($to < $from) {
                    app(SavedSearchAlertService::class)->notifyPriceDrop($listing->fresh(), $from, $to);
                }
                Offer::query()
                    ->where('listing_id', $listing->id)
                    ->whereKeyNot($offer->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'rejected']);
            }
        }

        $this->send(
            $conversation,
            $seller,
            $status === 'accepted' ? 'Offer accepted.' : 'Offer declined.',
            'system',
        );

        return $offer->fresh();
    }

    public function markRead(Conversation $conversation, User $reader): void
    {
        $this->assertParticipant($conversation, $reader);

        $updated = $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            broadcast(new MessagesRead($conversation, $reader))->toOthers();
        }
    }

    public function unreadCount(User $user): int
    {
        return Message::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->whereHas('conversation', function ($q) use ($user): void {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            })
            ->count();
    }

    private function assertParticipant(Conversation $conversation, User $user): void
    {
        if (! in_array($user->id, [$conversation->buyer_id, $conversation->seller_id], true)) {
            abort(403, 'Not a participant of this chat.');
        }
    }

    private function assertNotBlocked(int $a, int $b): void
    {
        $blocked = UserBlock::query()
            ->where(function ($q) use ($a, $b): void {
                $q->where('blocker_id', $a)->where('blocked_id', $b);
            })
            ->orWhere(function ($q) use ($a, $b): void {
                $q->where('blocker_id', $b)->where('blocked_id', $a);
            })
            ->exists();

        if ($blocked) {
            throw ValidationException::withMessages(['user' => 'Chat is unavailable with this user.']);
        }
    }
}
