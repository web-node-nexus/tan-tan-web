<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ListingResource;
use App\Models\Listing;
use App\Models\ListingPromotion;
use App\Models\Package;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoostController extends Controller
{
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $data = $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
        ]);

        $package = Package::query()->whereKey($data['package_id'])->where('is_active', true)->firstOrFail();

        $listing = DB::transaction(function () use ($request, $listing, $package): Listing {
            $starts = now();
            $ends = now()->addDays(max(1, (int) $package->duration_days));

            ListingPromotion::query()->create([
                'listing_id' => $listing->id,
                'user_id' => $request->user()->id,
                'package_id' => $package->id,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'status' => 'active',
            ]);

            $listing->forceFill([
                'is_featured' => true,
                'featured_until' => $ends,
            ])->save();

            return $listing->fresh(['images', 'category', 'city.state', 'user']);
        });

        return ApiResponse::success(
            (new ListingResource($listing))->resolve(),
            __('api.boosts.applied'),
            201,
        );
    }
}
