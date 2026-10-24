<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ListingResource;
use App\Models\Listing;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ids = $request->user()->favourites()->pluck('listing_id');
        $page = Listing::query()
            ->with(['category', 'city.state', 'images', 'user:id,name,is_verified'])
            ->with(['favourites' => fn ($q) => $q->where('user_id', $request->user()->id)])
            ->whereIn('id', $ids)
            ->active()
            ->latest('published_at')
            ->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            ListingResource::collection($page)->resolve(),
            __('api.listings.favourites_ok'),
            200,
            [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }
}
