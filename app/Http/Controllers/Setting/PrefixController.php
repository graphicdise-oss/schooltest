<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Prefix;
use Illuminate\Http\Request;

class PrefixController extends Controller
{
    // หน้ารายการคำนำหน้า
    public function index(Request $request)
    {
        $query = Prefix::query();

        if ($request->filled('role_filter')) {
            $query->where('role', $request->role_filter);
        }

        $prefixes = $query->orderBy('sort_order', 'asc')->get();

        return view('settings.prefix_index', compact('prefixes'));
    }

    // บันทึกคำนำหน้าใหม่
    public function store(Request $request)
    {
        $request->validate([
            'name_th' => 'required|string|max:100',
            'role'    => 'required|in:student,personnel,all',
        ]);

        Prefix::create([
            'name_th'    => $request->name_th,
            'name_en'    => $request->name_en,
            'role'       => $request->role,
            'is_active'  => true,
            'sort_order' => Prefix::max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'เพิ่มคำนำหน้าสำเร็จ');
    }

    // อัปเดตคำนำหน้า
    public function update(Request $request, $id)
    {
        $prefix = Prefix::findOrFail($id);

        $request->validate([
            'name_th' => 'required|string|max:100',
            'role'    => 'required|in:student,personnel,all',
        ]);

        $prefix->update($request->only(['name_th', 'name_en', 'role']));

        return redirect()->back()->with('success', 'แก้ไขคำนำหน้าสำเร็จ');
    }

    // เปิด/ปิด สถานะ
    public function toggle($id)
    {
        $prefix = Prefix::findOrFail($id);
        $prefix->update(['is_active' => !$prefix->is_active]);

        return redirect()->back()->with('success', 'เปลี่ยนสถานะสำเร็จ');
    }

    // ลบ
    public function destroy($id)
    {
        Prefix::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบคำนำหน้าสำเร็จ');
    }
}