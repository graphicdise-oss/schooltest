<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureParentAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('parent')->check()) {
            return redirect()->route('parent.login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }
        return $next($request);
    }
}
