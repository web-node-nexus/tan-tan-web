<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatalogNode;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $nodes = CatalogNode::query()->orderBy('sort_order')->orderBy('id')->get();
        $byParent = $nodes->groupBy(fn (CatalogNode $node): string => (string) ($node->parent_id ?? 'root'));

        $build = function (int|string $parentId) use (&$build, $byParent): array {
            return ($byParent->get((string) $parentId) ?? collect())->map(function (CatalogNode $node) use ($build): array {
                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'children' => $build($node->id),
                ];
            })->values()->all();
        };

        $trees = [];
        foreach ($nodes->whereNull('parent_id')->pluck('root')->unique() as $root) {
            $trees[$root] = ($byParent->get('root') ?? collect())
                ->where('root', $root)
                ->map(fn (CatalogNode $node): array => [
                    'id' => $node->id,
                    'name' => $node->name,
                    'children' => $build($node->id),
                ])
                ->values()
                ->all();
        }

        return ApiResponse::success($trees, __('api.catalog.list_ok'));
    }
}
