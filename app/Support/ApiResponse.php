<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

final class ApiResponse
{
    /**
     * @param  array<string, mixed>|JsonResource|ResourceCollection|AbstractPaginator|AbstractCursorPaginator|null  $data
     * @param  array<string, mixed>  $meta
     */
    public static function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        if ($data instanceof AbstractPaginator || $data instanceof AbstractCursorPaginator) {
            $payload = $data->items();
            $meta = array_merge(self::paginationMeta($data), $meta);
        } elseif ($data instanceof ResourceCollection && $data->resource instanceof AbstractPaginator) {
            $payload = $data;
            $meta = array_merge(self::paginationMeta($data->resource), $meta);
        } else {
            $payload = $data;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $payload,
            'meta' => (object) $meta,
        ], $status);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     * @param  array<string, mixed>  $meta
     */
    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null,
        array $meta = [],
    ): JsonResponse {
        $body = [
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => (object) $meta,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /**
     * @return array<string, mixed>
     */
    private static function paginationMeta(AbstractPaginator|AbstractCursorPaginator $paginator): array
    {
        if ($paginator instanceof AbstractCursorPaginator) {
            return [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'has_more' => $paginator->hasMorePages(),
            ];
        }

        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
