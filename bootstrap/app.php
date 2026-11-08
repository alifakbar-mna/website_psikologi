<?php

use App\Http\Middleware\LogVisit;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            LogVisit::class,
        ]);

        $middleware->alias([
            'permission' => RequirePermission::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(
            function (PostTooLargeException $exception, Request $request) {
                $message = 'Total data upload melebihi batas server. '
                    .'Kurangi ukuran atau jumlah file, lalu coba kembali.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                    ], 413);
                }

                return response()->view(
                    'errors.413',
                    ['message' => $message],
                    413
                );
            }
        );
    })
    ->create();