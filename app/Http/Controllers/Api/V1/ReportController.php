<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Filament\Support\ListingModeration;
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

        $report = Report::query()->firstOrCreate(
            [
                'reporter_id' => $request->user()->id,
                'listing_id' => $listing->id,
            ],
            [
                'reason' => $data['reason'],
                'details' => $data['details'] ?? null,
                'status' => 'open',
            ],
        );

        $count = Report::query()
            ->where('listing_id', $listing->id)
            ->whereIn('status', ['open', 'reviewing'])
            ->distinct()
            ->count('reporter_id');

        $threshold = max(1, (int) config('services.tantan.report_hide_threshold', 5));
        $hidden = $listing->status === 'blocked';
        if (! $hidden && $count >= $threshold && in_array($listing->status, ['active', 'pending'], true)) {
            ListingModeration::applyStatus($listing, 'blocked', 'Auto-hidden after '.$count.' distinct reports.');
            $hidden = true;
        }

        return ApiResponse::success([
            'hidden' => $hidden,
            'report_count' => $count,
            'already_reported' => ! $report->wasRecentlyCreated,
        ], $report->wasRecentlyCreated ? __('api.reports.created') : __('api.reports.duplicate'), $report->wasRecentlyCreated ? 201 : 200);
    }
}
