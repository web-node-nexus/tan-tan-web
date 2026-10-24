<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function forUser(User $user): JsonResponse
    {
        $reviews = Review::query()
            ->with('reviewer:id,name')
            ->where('reviewee_id', $user->id)
            ->latest('id')
            ->limit(40)
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'listing_id' => $review->listing_id,
                'reviewer' => [
                    'id' => $review->reviewer?->id,
                    'name' => $review->reviewer?->name,
                ],
                'created_at' => $review->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success([
            'rating_avg' => (float) $user->rating_avg,
            'rating_count' => (int) $user->rating_count,
            'reviews' => $reviews,
        ], __('api.reviews.list_ok'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reviewee_id' => ['required', 'integer', 'exists:users,id', 'different:'.$request->user()->id],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($data['listing_id'])) {
            $listing = Listing::query()->findOrFail($data['listing_id']);
            abort_unless(
                (int) $data['reviewee_id'] === (int) $listing->user_id,
                422,
                'Listing does not match the seller being reviewed.',
            );
        }

        $review = DB::transaction(function () use ($request, $data): Review {
            $review = Review::query()->updateOrCreate(
                [
                    'reviewer_id' => $request->user()->id,
                    'reviewee_id' => $data['reviewee_id'],
                    'listing_id' => $data['listing_id'] ?? null,
                ],
                [
                    'rating' => $data['rating'],
                    'comment' => $data['comment'] ?? null,
                ],
            );

            $stats = Review::query()
                ->where('reviewee_id', $data['reviewee_id'])
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
                ->first();

            User::query()->whereKey($data['reviewee_id'])->update([
                'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
                'rating_count' => (int) ($stats->total ?? 0),
            ]);

            return $review;
        });

        return ApiResponse::success([
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
        ], __('api.reviews.created'), 201);
    }
}
