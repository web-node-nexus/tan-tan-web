<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Report;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'reason' => ['required', 'string', 'max:64'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $listing = Listing::query()->findOrFail($data['listing_id']);
        abort_if($listing->user_id === $request->user()->id, 422, 'You cannot report your own ad.');

        Report::query()->create([
            'reporter_id' => $request->user()->id,
            'listing_id' => $listing->id,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        return ApiResponse::success(null, __('api.reports.created'), 201);
    }
}
