<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Geo\GooglePlacesService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function __construct(private readonly GooglePlacesService $places) {}

    public function suggest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        return ApiResponse::success(
            $this->places->suggest(
                trim($data['q']),
                isset($data['lat']) ? (float) $data['lat'] : null,
                isset($data['lng']) ? (float) $data['lng'] : null,
            ),
            __('api.places.suggest_ok'),
        );
    }

    public function reverse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $hit = $this->places->reverse((float) $data['lat'], (float) $data['lng']);
        if ($hit === null) {
            return ApiResponse::error(__('api.places.unavailable'), 404);
        }

        return ApiResponse::success($hit, __('api.places.reverse_ok'));
    }
}
