<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = Package::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Package $p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->localizedName(),
                'description' => $p->localizedDescription(),
                'price' => (float) $p->price,
                'duration_days' => $p->duration_days,
                'is_best_value' => $p->is_best_value,
                'is_featured_boost' => $p->is_featured_boost,
                'benefits' => $p->benefits ?? [],
            ]);

        return ApiResponse::success($packages, __('api.packages.list_ok'));
    }
}
