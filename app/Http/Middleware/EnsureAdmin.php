<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบ (admin) เท่านั้นที่เข้าถึงส่วนนี้ได้');
        }
        return $next($request);
    }
}
