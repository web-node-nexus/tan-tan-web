<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Offer;
use App\Services\Chat\ChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->chat->forUser($request->user());

        return ApiResponse::success(
            ConversationResource::collection($page)->resolve(),
            __('api.chat.list_ok'),
            200,
            [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
                'unread_total' => $this->chat->unreadCount($request->user()),
            ],
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $listing = Listing::query()->findOrFail($data['listing_id']);
        $conversation = $this->chat->start($request->user(), $listing, $data['message'] ?? null);

        return ApiResponse::success(
            (new ConversationResource($conversation))->resolve(),
            __('api.chat.started'),
            201,
        );
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless(in_array($request->user()->id, [$conversation->buyer_id, $conversation->seller_id], true), 403);

        $conversation->load([
            'listing.images',
            'listing.category',
            'buyer:id,name',
            'seller:id,name',
            'messages' => fn ($q) => $q->latest('id')->limit(1),
        ]);
        $this->chat->markRead($conversation, $request->user());

        return ApiResponse::success(
            (new ConversationResource($conversation))->resolve(),
            __('api.chat.show_ok'),
        );
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless(in_array($request->user()->id, [$conversation->buyer_id, $conversation->seller_id], true), 403);

        $this->chat->markRead($conversation, $request->user());
        $page = $this->chat->messages($conversation, (int) $request->integer('per_page', 50));

        $items = collect($page->items())->sortBy('id')->values();

        return ApiResponse::success(
            MessageResource::collection($items)->resolve(),
            __('api.chat.messages_ok'),
            200,
            [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,pdf,txt,doc,docx'],
        ]);

        abort_if(
            empty($data['body']) && ! $request->hasFile('attachment'),
            422,
            'Message body or attachment is required.',
        );

        $message = $this->chat->send(
            $conversation,
            $request->user(),
            $data['body'] ?? null,
            'text',
            $request->file('attachment'),
        );

        return ApiResponse::success(
            (new MessageResource($message))->resolve(),
            __('api.chat.sent'),
            201,
        );
    }

    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $this->chat->typing($conversation, $request->user());

        return ApiResponse::success(null, __('api.chat.typing'));
    }

    public function offer(Request $request, Conversation $conversation): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999.99'],
        ]);

        $offer = $this->chat->makeOffer($conversation, $request->user(), (float) $data['amount']);

        return ApiResponse::success([
            'id' => $offer->id,
            'amount' => (float) $offer->amount,
            'status' => $offer->status,
        ], __('api.chat.offer_sent'), 201);
    }

    public function respondOffer(Request $request, Offer $offer): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        $offer = $this->chat->respondOffer($offer, $request->user(), $data['status']);

        return ApiResponse::success([
            'id' => $offer->id,
            'amount' => (float) $offer->amount,
            'status' => $offer->status,
        ], __('api.chat.offer_updated'));
    }
}
