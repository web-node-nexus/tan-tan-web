<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbOk = false;

        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
        }

        return ApiResponse::success([
            'app' => config('app.name'),
            'env' => config('app.env'),
            'version' => 'v1',
            'database' => $dbOk ? 'ok' : 'unavailable',
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
        ], __('api.health_ok'));
    }
}
