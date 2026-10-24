<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = SavedSearch::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->get()
            ->map(fn (SavedSearch $row) => [
                'id' => $row->id,
                'name' => $row->name,
                'filters' => $row->filters,
                'notify' => $row->notify,
                'created_at' => $row->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success($items, __('api.saved_searches.list_ok'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'filters' => ['required', 'array'],
            'notify' => ['sometimes', 'boolean'],
        ]);

        $row = SavedSearch::query()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'filters' => $data['filters'],
            'notify' => $data['notify'] ?? true,
        ]);

        return ApiResponse::success([
            'id' => $row->id,
            'name' => $row->name,
            'filters' => $row->filters,
            'notify' => $row->notify,
        ], __('api.saved_searches.created'), 201);
    }

    public function destroy(Request $request, SavedSearch $savedSearch): JsonResponse
    {
        abort_unless($savedSearch->user_id === $request->user()->id, 403);
        $savedSearch->delete();

        return ApiResponse::success(null, __('api.saved_searches.deleted'));
    }
}
