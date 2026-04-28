<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ResponseBuilder
{
    public function success(mixed $data, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'error'   => null,
        ], $code);
    }

    public function error(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'error'   => $message,
        ], $code);
    }

    public function paginated(\Illuminate\Database\Eloquent\Builder $query, int $code = 200): JsonResponse
    {
        $paginator = $query->paginate();

        return response()->json([
            'success' => true,
            'data'    => [
                'items'        => $paginator->items(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'error'   => null,
        ], $code);
    }
}
