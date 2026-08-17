<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::orderBy('sort_order')->orderBy('id')->paginate(20);
        return view('settings.leave_types', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_name' => 'required|string|max:100',
            'abbr_th'         => 'nullable|string|max:20',
            'name_en'         => 'nullable|string|max:100',
            'abbr_en'         => 'nullable|string|max:20',
        ], [
            'leave_type_name.required' => 'กรุณากรอกชื่อประเภทการลา',
        ]);

        LeaveType::create([
            'leave_type_key'  => $this->generateUniqueKey($request->leave_type_name),
            'leave_type_name' => $request->leave_type_name,
            'abbr_th'         => $request->abbr_th,
            'name_en'         => $request->name_en,
            'abbr_en'         => $request->abbr_en,
            'is_active'       => true,
            'sort_order'      => (LeaveType::max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('success', 'เพิ่มประเภทการลาสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'leave_type_name' => 'required|string|max:100',
            'abbr_th'         => 'nullable|string|max:20',
            'name_en'         => 'nullable|string|max:100',
            'abbr_en'         => 'nullable|string|max:20',
        ], [
            'leave_type_name.required' => 'กรุณากรอกชื่อประเภทการลา',
        ]);

        // leave_type_key ไม่แก้ไข เพราะมีการอ้างอิงจากคำขอลาที่มีอยู่แล้ว
        LeaveType::findOrFail($id)->update([
            'leave_type_name' => $request->leave_type_name,
            'abbr_th'         => $request->abbr_th,
            'name_en'         => $request->name_en,
            'abbr_en'         => $request->abbr_en,
        ]);

        return back()->with('success', 'แก้ไขประเภทการลาสำเร็จ');
    }

    public function toggle($id)
    {
        $type = LeaveType::findOrFail($id);
        $type->update(['is_active' => ! $type->is_active]);
        return back();
    }

    public function destroy($id)
    {
        LeaveType::findOrFail($id)->delete();
        return back()->with('success', 'ลบประเภทการลาสำเร็จ');
    }

    private function generateUniqueKey(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'leave_type';
        $key  = $base;
        $i    = 1;
        while (LeaveType::where('leave_type_key', $key)->exists()) {
            $key = $base . '_' . (++$i);
        }
        return $key;
    }
}
