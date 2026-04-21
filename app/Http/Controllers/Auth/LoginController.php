<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1. ตรวจสอบข้อมูลที่ส่งมา
        $credentials = $request->validate([
            'employee_code' => 'required',
            'password' => 'required',
        ]);

        // 2. พยายาม Login (Laravel จะจัดการเช็ค Password ที่เข้ารหัสให้เอง)
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Login สำเร็จ ไปหน้า Dashboard
            return redirect()->intended('dashboard');
        }

        // 3. ถ้าผิดพลาด ส่งกลับพร้อมข้อความ
        return back()->withErrors([
            'employee_code' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}