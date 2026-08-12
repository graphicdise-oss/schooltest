<?php

namespace App\Http\Controllers;

use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// เปลี่ยนรหัสผ่านของผู้ใช้เอง (ทุก role) — ยึดตัวตนจาก Auth::id() เสมอ ไม่รับ id จาก request/route
// ส่วนแก้ไขข้อมูลส่วนตัวอื่นๆ ใช้หน้า personnels.edit/update ร่วมกับแอดมิน (ดู PersonnelController)
class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $personnel = Personnel::findOrFail(Auth::id());

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $personnel->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านเดิมไม่ถูกต้อง'])->withInput();
        }

        $personnel->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('personnels.edit', $personnel->personnel_id)->with('success', 'เปลี่ยนรหัสผ่านสำเร็จ');
    }
}
