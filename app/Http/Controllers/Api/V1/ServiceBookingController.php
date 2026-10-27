<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ServiceBookingResource;
use App\Models\Listing;
use App\Models\ServiceBooking;
use App\Support\ApiResponse;
use App\Support\BookingDetails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceBookingController extends Controller
{
    public function store(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->status === 'active', 404);
        $listing->loadMissing('category');
        abort_unless($listing->category?->kind === 'service', 422);

        if ($listing->user_id === $request->user()->id) {
            return ApiResponse::error(__('api.bookings.own_listing'), 422);
        }

        $data = $request->validate(BookingDetails::rules($listing->category) + [
            'visit_on' => ['required', 'date', 'after_or_equal:today'],
            'slot' => ['required', Rule::in(ServiceBooking::SLOTS)],
            'note' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $exists = ServiceBooking::query()
            ->where('listing_id', $listing->id)
            ->where('user_id', $request->user()->id)
            ->whereDate('visit_on', $data['visit_on'])
            ->where('slot', $data['slot'])
            ->exists();

        if ($exists) {
            return ApiResponse::error(__('api.bookings.already_booked'), 409);
        }

        $booking = ServiceBooking::query()->create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'visit_on' => $data['visit_on'],
            'slot' => $data['slot'],
            'note' => $data['note'],
            'details' => BookingDetails::onlyKnown($listing->category, is_array($data['details'] ?? null) ? $data['details'] : []),
            'status' => 'requested',
        ]);
        $booking->load('user:id,name');

        return ApiResponse::success(
            (new ServiceBookingResource($booking))->resolve(),
            __('api.bookings.requested'),
            201,
        );
    }

    public function index(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $bookings = ServiceBooking::query()
            ->where('listing_id', $listing->id)
            ->with('user:id,name')
            ->orderBy('visit_on')
            ->get();

        return ApiResponse::success(
            ServiceBookingResource::collection($bookings)->resolve(),
            __('api.bookings.list_ok'),
        );
    }

    public function mine(Request $request): JsonResponse
    {
        $bookings = ServiceBooking::query()
            ->where('user_id', $request->user()->id)
            ->with('listing:id,title')
            ->latest()
            ->get();

        return ApiResponse::success(
            ServiceBookingResource::collection($bookings)->resolve(),
            __('api.bookings.mine_ok'),
        );
    }

    public function update(Request $request, ServiceBooking $booking): JsonResponse
    {
        $booking->load('listing');
        abort_unless($booking->listing?->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(ServiceBooking::PROVIDER_STATUSES)],
            'quoted_amount' => ['required_if:status,accepted', 'nullable', 'integer', 'min:1', 'max:999999'],
        ]);

        $payload = ['status' => $data['status']];
        if ($data['status'] === 'accepted') {
            $payload['quoted_amount'] = $data['quoted_amount'];
        }

        $booking->update($payload);
        $booking->load('user:id,name');

        return ApiResponse::success(
            (new ServiceBookingResource($booking))->resolve(),
            __('api.bookings.updated'),
        );
    }

    public function cancel(Request $request, ServiceBooking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless(in_array($booking->status, ['requested', 'accepted'], true), 422);

        $booking->update(['status' => 'cancelled']);
        $booking->load('listing:id,title');

        return ApiResponse::success(
            (new ServiceBookingResource($booking))->resolve(),
            __('api.bookings.updated'),
        );
    }
}
