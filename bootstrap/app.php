<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'parent.auth' => \App\Http\Middleware\EnsureParentAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('parent/*')
                && ! $request->expectsJson()
                && ! $e instanceof \Illuminate\Validation\ValidationException) {
                return redirect()->route('parent.login')
                    ->with('error', 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
            }
        });
    })->create();
