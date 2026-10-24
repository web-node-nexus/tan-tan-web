<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = UserBlock::query()
            ->with('blocked:id,name,phone')
            ->where('blocker_id', $request->user()->id)
            ->latest('id')
            ->get()
            ->map(fn (UserBlock $block) => [
                'id' => $block->id,
                'user' => [
                    'id' => $block->blocked?->id,
                    'name' => $block->blocked?->name,
                ],
                'created_at' => $block->created_at?->toIso8601String(),
            ]);

        return ApiResponse::success($rows, __('api.blocks.list_ok'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', 'different:'.$request->user()->id],
        ]);

        $block = UserBlock::query()->firstOrCreate([
            'blocker_id' => $request->user()->id,
            'blocked_id' => $data['user_id'],
        ]);

        return ApiResponse::success([
            'id' => $block->id,
            'user_id' => $block->blocked_id,
        ], __('api.blocks.created'), 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        UserBlock::query()
            ->where('blocker_id', $request->user()->id)
            ->where('blocked_id', $user->id)
            ->delete();

        return ApiResponse::success(null, __('api.blocks.removed'));
    }
}
