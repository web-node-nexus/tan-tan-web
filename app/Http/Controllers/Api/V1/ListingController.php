<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreListingRequest;
use App\Http\Requests\Api\V1\UpdateListingRequest;
use App\Http\Resources\Api\V1\ListingResource;
use App\Models\Listing;
use App\Services\Listings\ListingQuotaService;
use App\Services\Listings\ListingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(
        private readonly ListingService $listings,
        private readonly ListingQuotaService $quota,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->query();
        $viewer = $request->user('sanctum') ?? auth('sanctum')->user();
        if ($viewer) {
            $filters['user_id'] = $viewer->id;
        }

        $page = $this->listings->search($filters, (int) $request->integer('per_page', 20));

        return ApiResponse::success(
            ListingResource::collection($page)->resolve(),
            __('api.listings.list_ok'),
            200,
            [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function show(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('view', $listing);

        $listing->load(['category', 'city.state', 'images', 'user', 'favourites']);
        $viewer = $request->user('sanctum') ?? auth('sanctum')->user();
        $this->listings->incrementViews($listing, $viewer);

        return ApiResponse::success(
            (new ListingResource($listing->fresh(['category', 'city.state', 'images', 'user', 'favourites'])))->resolve(),
            __('api.listings.show_ok'),
        );
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $this->authorize('create', Listing::class);

        $validated = $request->validated();
        $paymentId = isset($validated['payment_id']) ? (int) $validated['payment_id'] : null;
        unset($validated['payment_id']);

        $listing = $this->listings->create(
            $request->user(),
            $validated,
            $this->uploadedImages($request),
            $paymentId,
        );

        return ApiResponse::success(
            (new ListingResource($listing))->resolve(),
            __('api.listings.created'),
            201,
        );
    }

    public function quota(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->quota->quotaFor($request->user()),
            __('api.listings.quota_ok'),
        );
    }

    public function purchaseExtraSlot(Request $request): JsonResponse
    {
        $payment = $this->quota->purchaseExtraAdCredit($request->user());

        return ApiResponse::success([
            'payment_id' => $payment->id,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'purpose' => $payment->purpose,
            'quota' => $this->quota->quotaFor($request->user()),
        ], __('api.listings.extra_slot_paid', [
            'fee' => number_format((float) $payment->amount, 0),
        ]), 201);
    }

    public function update(UpdateListingRequest $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $listing = $this->listings->update(
            $listing,
            $request->validated(),
            $this->uploadedImages($request),
        );

        return ApiResponse::success(
            (new ListingResource($listing))->resolve(),
            __('api.listings.updated'),
        );
    }

    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);
        $this->listings->delete($listing);

        return ApiResponse::success(null, __('api.listings.deleted'));
    }

    public function mine(Request $request): JsonResponse
    {
        $page = $this->listings->mine(
            $request->user(),
            $request->query('status'),
            (int) $request->integer('per_page', 20),
        );

        return ApiResponse::success(
            ListingResource::collection($page)->resolve(),
            __('api.listings.mine_ok'),
            200,
            [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function markSold(Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);
        $listing = $this->listings->markSold($listing);

        return ApiResponse::success(
            (new ListingResource($listing))->resolve(),
            __('api.listings.sold'),
        );
    }

    public function toggleFavourite(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->status === 'active', 404);

        $favourited = $this->listings->toggleFavourite($request->user(), $listing);

        return ApiResponse::success(
            ['is_favourited' => $favourited],
            $favourited ? __('api.listings.favourited') : __('api.listings.unfavourited'),
        );
    }

    public function recentlyViewed(Request $request): JsonResponse
    {
        $page = $this->listings->recentlyViewed($request->user(), (int) $request->integer('per_page', 20));

        return ApiResponse::success(
            ListingResource::collection($page)->resolve(),
            __('api.listings.recent_ok'),
            200,
            [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }

    public function renew(Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);
        $listing = $this->listings->renew($listing);

        return ApiResponse::success(
            (new ListingResource($listing))->resolve(),
            __('api.listings.renewed'),
        );
    }

    public function reserve(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);
        $data = $request->validate([
            'reserved' => ['required', 'boolean'],
        ]);
        $listing = $this->listings->reserve($listing, (bool) $data['reserved']);

        return ApiResponse::success(
            (new ListingResource($listing))->resolve(),
            $data['reserved'] ? __('api.listings.reserved') : __('api.listings.unreserved'),
        );
    }

    public function insights(Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        return ApiResponse::success(
            $this->listings->insights($listing),
            __('api.listings.insights_ok'),
        );
    }

    /**
     * @return array<int, \Illuminate\Http\UploadedFile>
     */
    private function uploadedImages(Request $request): array
    {
        $files = $request->file('images', []);
        if ($files instanceof \Illuminate\Http\UploadedFile) {
            return [$files];
        }

        return is_array($files) ? array_values(array_filter($files)) : [];
    }
}
