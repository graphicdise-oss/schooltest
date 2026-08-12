<?php

namespace App\Http\Controllers;

use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

// หน้าแก้ไขข้อมูลส่วนตัวของผู้ใช้เอง (ทุก role) — ต่างจาก personnels.edit ที่เป็นเครื่องมือของแอดมิน
// สำหรับแก้ไขข้อมูลคนอื่น เมธอดในนี้จึงยึดตัวตนจาก Auth::id() เสมอ ไม่รับ id จาก request/route
class ProfileController extends Controller
{
    public function edit()
    {
        $personnel = Personnel::findOrFail(Auth::id());

        return view('personnel.profile_edit', compact('personnel'));
    }

    public function update(Request $request)
    {
        $personnel = Personnel::findOrFail(Auth::id());

        $request->validate([
            'thai_prefix' => 'nullable|string|max:50',
            'thai_firstname' => 'required|string|max:100',
            'thai_lastname' => 'required|string|max:100',
            'eng_firstname' => 'nullable|string|max:100',
            'eng_lastname' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'nationality' => 'nullable|string|max:50',
            'ethnicity' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $data = $request->only([
            'thai_prefix', 'thai_firstname', 'thai_lastname', 'eng_firstname', 'eng_lastname',
            'phone', 'email', 'date_of_birth', 'blood_group', 'nationality', 'ethnicity', 'religion',
        ]);

        if ($request->hasFile('photo')) {
            if ($personnel->personnel_image) {
                Storage::disk('public')->delete($personnel->personnel_image);
            }
            $data['personnel_image'] = $request->file('photo')->store('personnels', 'public');
        }

        $personnel->update($data);

        return redirect()->route('profile.edit')->with('success', 'บันทึกข้อมูลส่วนตัวสำเร็จ');
    }

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

        return redirect()->route('profile.edit')->with('success', 'เปลี่ยนรหัสผ่านสำเร็จ');
    }
}
