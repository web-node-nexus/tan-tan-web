<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ListingResource;
use App\Models\Listing;
use App\Models\SellerFollow;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function show(Request $request, User $user): JsonResponse
    {
        $listings = Listing::query()
            ->with(['category', 'city.state', 'images', 'user'])
            ->where('user_id', $user->id)
            ->active()
            ->latest('published_at')
            ->limit(40)
            ->get();

        $following = false;
        $viewer = $request->user('sanctum') ?? auth('sanctum')->user();
        if ($viewer) {
            $following = SellerFollow::query()
                ->where('follower_id', $viewer->id)
                ->where('seller_id', $user->id)
                ->exists();
        }

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'is_verified' => (bool) $user->is_verified,
            'phone_verified' => $user->phone_verified_at !== null,
            'account_type' => $user->account_type ?: 'personal',
            'business_name' => $user->business_name,
            'bio' => $user->bio,
            'member_since' => $user->created_at?->toDateString(),
            'rating_avg' => (float) ($user->rating_avg ?? 0),
            'rating_count' => (int) ($user->rating_count ?? 0),
            'active_listings' => $listings->count(),
            'is_following' => $following,
            'listings' => ListingResource::collection($listings)->resolve(),
        ], __('api.sellers.show_ok'));
    }

    public function follow(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->id === $user->id, 422, 'You cannot follow yourself.');

        SellerFollow::query()->firstOrCreate([
            'follower_id' => $request->user()->id,
            'seller_id' => $user->id,
        ]);

        return ApiResponse::success(['is_following' => true], __('api.sellers.followed'), 201);
    }

    public function unfollow(Request $request, User $user): JsonResponse
    {
        SellerFollow::query()
            ->where('follower_id', $request->user()->id)
            ->where('seller_id', $user->id)
            ->delete();

        return ApiResponse::success(['is_following' => false], __('api.sellers.unfollowed'));
    }
}
