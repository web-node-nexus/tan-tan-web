<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AreaResource;
use App\Models\Area;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $cityId = $request->integer('city_id') ?: null;
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        $areas = Area::query()
            ->with('city:id,name')
            ->when($cityId, fn ($builder) => $builder->where('city_id', $cityId))
            ->when($q !== '', fn ($builder) => $builder->where('name', 'like', $q.'%'))
            ->orderBy('name')
            ->limit(60)
            ->get();

        if (is_numeric($lat) && is_numeric($lng)) {
            $lat = (float) $lat;
            $lng = (float) $lng;
            $areas = $areas
                ->sortBy(fn (Area $area): float => (($area->latitude - $lat) ** 2) + (($area->longitude - $lng) ** 2))
                ->values();
        }

        return ApiResponse::success(
            AreaResource::collection($areas)->resolve(),
            __('api.areas.list_ok'),
        );
    }
}
