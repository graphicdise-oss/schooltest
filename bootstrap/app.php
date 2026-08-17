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
            'track.activity' => \App\Http\Middleware\TrackFirstActivity::class,
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

            // ไม่ต้องการให้ผู้ใช้เห็นหน้า debug ของ Laravel (stack trace เต็มๆ) ไม่ว่า APP_DEBUG จะเปิดหรือปิด
            // ยกเว้น HTTP exception ปกติ (404/403/419/429/503 ฯลฯ) ที่มีหน้า errors/{code}.blade.php ของตัวเองอยู่แล้ว
            // ยกเว้น AuthenticationException (ยังไม่ได้ login) เพราะไม่ใช่ HttpExceptionInterface แต่ต้องปล่อยให้
            // Laravel จัดการแบบปกติ (เด้งไปหน้า login พร้อมจำหน้าที่ตั้งใจจะเข้าไว้ ไม่ใช่ทับด้วยหน้า error 500)
            // และยกเว้น TokenMismatchException (เซสชัน/CSRF token หมดอายุ เช่น เปิดฟอร์มค้างไว้นานแล้วค่อยกดส่ง
            // — พบตอนอัปโหลดไฟล์นำเข้าเกรดที่กรอกนาน) เพราะก็ไม่ใช่ HttpExceptionInterface เหมือนกัน แต่ Laravel
            // มีหน้า errors/419.blade.php ("เซสชันหมดอายุ") จัดการเรื่องนี้ให้อยู่แล้ว ไม่ควรถูกทับด้วยหน้า error 500
            if (! $request->expectsJson()
                && ! $e instanceof \Illuminate\Validation\ValidationException
                && ! $e instanceof \Illuminate\Auth\AuthenticationException
                && ! $e instanceof \Illuminate\Session\TokenMismatchException
                && ! $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return response()->view('errors.500', [], 500);
            }
        });
    })->create();
