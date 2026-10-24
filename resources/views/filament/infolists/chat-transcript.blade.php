@php
    $conversation = $getRecord();
    $messages = $conversation->messages()->with('sender:id,name')->orderBy('id')->limit(500)->get();
@endphp

<div class="tt-chat">
    @forelse ($messages as $message)
        @php $mine = $message->sender_id === $conversation->seller_id; @endphp
        <div @class(['tt-chat__row', 'tt-chat__row--seller' => $mine])>
            <div @class([
                'tt-chat__bubble',
                'tt-chat__bubble--seller' => $mine,
                'tt-chat__bubble--system' => $message->type === 'system',
                'tt-chat__bubble--offer' => $message->type === 'offer',
            ])>
                <p class="tt-chat__meta">
                    <span class="font-semibold">{{ $message->sender?->name ?? 'Deleted user' }}</span>
                    · {{ $mine ? 'Seller' : 'Buyer' }}
                    @if ($message->type !== 'text')
                        · <span class="uppercase">{{ $message->type }}</span>
                    @endif
                </p>
                @if (filled($message->body))
                    <p class="tt-chat__body">{{ $message->body }}</p>
                @endif
                @if ($message->attachment_path)
                    <a href="{{ $message->attachment_url }}" target="_blank" rel="noopener" class="tt-chat__file">
                        📎 {{ $message->attachment_name ?? 'Attachment' }}
                    </a>
                @endif
                <p class="tt-chat__time">
                    {{ $message->created_at?->timezone('Asia/Kolkata')->format('d M, H:i') }}
                    @if ($message->read_at) · Read @endif
                </p>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">No messages in this thread.</p>
    @endforelse
</div>
