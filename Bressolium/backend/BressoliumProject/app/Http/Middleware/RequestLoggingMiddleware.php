<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = hrtime(true);

        $response = $next($request);

        $durationMs = (hrtime(true) - $startTime) / 1_000_000;

        Log::info('API Request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'user_id' => $request->user()?->id ?? null,
            'status' => $response->getStatusCode(),
            'duration_ms' => round($durationMs, 2),
        ]);

        return $response;
    }
}
