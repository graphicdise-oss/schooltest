<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('parent')->check()) {
            return redirect()->route('parent.dashboard');
        }
        return view('parent.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'student_code' => 'required|string',
            'password'     => 'required|string',
        ], [
            'student_code.required' => 'กรุณากรอกรหัสนักเรียน',
            'password.required'     => 'กรุณากรอกรหัสผ่าน',
        ]);

        try {
            if (Auth::guard('parent')->attempt([
                'student_code' => $request->student_code,
                'password'     => $request->password,
            ], $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('parent.dashboard'));
            }
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput($request->only('student_code'))
                ->with('error', 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
        }

        return back()->withInput($request->only('student_code'))
            ->with('error', 'รหัสนักเรียนหรือรหัสผ่านไม่ถูกต้อง');
    }

    public function logout(Request $request)
    {
        Auth::guard('parent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('parent.login')->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }
}
