<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CityResource;
use App\Models\City;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $cities = City::query()
            ->with('state')
            ->where('is_active', true)
            ->when($q !== '', fn ($builder) => $builder->where('name', 'like', $q.'%'))
            ->orderBy('name')
            ->limit(40)
            ->get();

        return ApiResponse::success(
            CityResource::collection($cities)->resolve(),
            __('api.cities.list_ok'),
        );
    }
}
