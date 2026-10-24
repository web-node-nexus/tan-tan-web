<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum', 'web']]);

Broadcast::channel('conversation.{id}', function ($user, int $id) {
    $conversation = Conversation::query()->find($id);
    if (! $conversation) {
        return false;
    }

    return in_array((int) $user->id, [(int) $conversation->buyer_id, (int) $conversation->seller_id], true)
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
}, ['guards' => ['sanctum', 'web']]);
