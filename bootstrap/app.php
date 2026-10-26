<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'adminMiddle' => AdminMiddleware::class,
        ]);

        // tamu dialihkan ke login admin sebelum route model binding, jadi tidak bisa menebak nomor pesanan dari 404
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: AdminMiddleware::class);

        $middleware->redirectUsersTo('/');

        $middleware->validateCsrfTokens(except: ['pembayaran/notifikasi']);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
