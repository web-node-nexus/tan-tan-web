<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use App\Models\UserAlert;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alerts = UserAlert::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->limit(40)
            ->get()
            ->map(fn (UserAlert $alert): array => [
                'id' => $alert->id,
                'title' => $alert->title,
                'body' => $alert->body,
                'listing_id' => $alert->listing_id,
                'read_at' => $alert->read_at?->toIso8601String(),
                'created_at' => $alert->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success([
            'unread' => UserAlert::query()->where('user_id', $request->user()->id)->whereNull('read_at')->count(),
            'alerts' => $alerts,
        ], __('api.alerts.list_ok'));
    }

    public function read(Request $request): JsonResponse
    {
        UserAlert::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, __('api.alerts.read_ok'));
    }

    public function pushToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);

        PushToken::query()->updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? null,
            ],
        );

        return ApiResponse::success(null, __('api.alerts.token_ok'));
    }
}
