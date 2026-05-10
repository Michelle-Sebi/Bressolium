<?php

use App\Exceptions\DomainException;
use App\Http\Middleware\ForceJsonMiddleware;
use App\Http\Middleware\RequestLoggingMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->redirectGuestsTo(fn () => null);
        $middleware->appendToGroup('api', [
            ForceJsonMiddleware::class,
            RequestLoggingMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/v1/*'));

        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'No autenticado.',
            ], 401);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Recurso no encontrado.',
            ], 404);
        });

        $exceptions->render(function (DomainException $e, $request) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ], $e->getCode());
        });
    })->create();
