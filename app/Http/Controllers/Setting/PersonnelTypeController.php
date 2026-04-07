<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Personne\PersonnelType;
use App\Models\Personne\PersonnelTypePermission;
use Illuminate\Http\Request;

class PersonnelTypeController extends Controller
{
    // รายการเมนูทั้งหมดที่จะให้กำหนดสิทธิ์
    private function getMenuList()
    {
        return [
            'ข้อมูลบุคคล' => [
                ['key' => 'students.index', 'label' => 'ข้อมูลนักเรียน'],
                ['key' => 'students.create', 'label' => 'เพิ่มนักเรียน'],
                ['key' => 'personnels.index', 'label' => 'ข้อมูลบุคลากร'],
                ['key' => 'personnels.create', 'label' => 'เพิ่มบุคลากร'],
            ],
            'วิชาการ' => [
                ['key' => 'academic.curriculum', 'label' => 'จัดการหลักสูตร'],
                ['key' => 'academic.timetable', 'label' => 'ตารางสอน'],
                ['key' => 'academic.scores', 'label' => 'บันทึกคะแนน'],
                ['key' => 'academic.reports', 'label' => 'รายงานวิชาการ'],
            ],
            'กิจการนักเรียน' => [
                ['key' => 'affairs.attendance', 'label' => 'เช็คชื่อ/ลา'],
                ['key' => 'affairs.behavior', 'label' => 'ความประพฤติ'],
                ['key' => 'affairs.sdq', 'label' => 'แบบประเมิน SDQ'],
                ['key' => 'affairs.homevisit', 'label' => 'เยี่ยมบ้าน'],
            ],
            'บริหารทั่วไป' => [
                ['key' => 'admin.news', 'label' => 'ประชาสัมพันธ์'],
                ['key' => 'admin.library', 'label' => 'ห้องสมุด'],
                ['key' => 'admin.bus', 'label' => 'School Bus'],
            ],
            'บัญชี/การเงิน' => [
                ['key' => 'finance.income', 'label' => 'ระบบบัญชีรายรับ'],
                ['key' => 'finance.expense', 'label' => 'ระบบบัญชีรายจ่าย'],
                ['key' => 'finance.salary', 'label' => 'ระบบเงินเดือน'],
                ['key' => 'finance.reports', 'label' => 'รายงานบัญชี'],
            ],
            'ตั้งค่า' => [
                ['key' => 'settings.prefix', 'label' => 'ตั้งค่าคำนำหน้า'],
                ['key' => 'settings.personnel_type', 'label' => 'ตั้งค่าประเภทบุคลากร'],
                ['key' => 'settings.general', 'label' => 'ตั้งค่าทั่วไป'],
            ],
        ];
    }

    // หน้ารายการ
    public function index()
    {
        $types = PersonnelType::orderBy('sort_order')->get();
        return view('settings.personnel_type_index', compact('types'));
    }

    // บันทึกใหม่
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        PersonnelType::create([
            'name'       => $request->name,
            'is_active'  => true,
            'sort_order' => PersonnelType::max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'เพิ่มประเภทบุคลากรสำเร็จ');
    }

    // แก้ไข
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        PersonnelType::findOrFail($id)->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }

    // เปิด/ปิด
    public function toggle($id)
    {
        $type = PersonnelType::findOrFail($id);
        $type->update(['is_active' => !$type->is_active]);
        return redirect()->back();
    }

    // ลบ
    public function destroy($id)
    {
        PersonnelType::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบสำเร็จ');
    }

    // ===== หน้ากำหนดสิทธิ์ =====
    public function permissions($id)
    {
        $type = PersonnelType::with('permissions')->findOrFail($id);
        $menuList = $this->getMenuList();

        // สร้าง map สิทธิ์ที่มีอยู่
        $existingPerms = $type->permissions->keyBy('menu_key');

        return view('settings.personnel_type_permissions', compact('type', 'menuList', 'existingPerms'));
    }

    // บันทึกสิทธิ์
    public function savePermissions(Request $request, $id)
    {
        $type = PersonnelType::findOrFail($id);
        $menuList = $this->getMenuList();
        $allowed = $request->input('permissions', []);

        // วนทุกเมนูแล้ว update/create
        foreach ($menuList as $group => $menus) {
            foreach ($menus as $menu) {
                PersonnelTypePermission::updateOrCreate(
                    ['type_id' => $type->type_id, 'menu_key' => $menu['key']],
                    [
                        'menu_label' => $menu['label'],
                        'menu_group' => $group,
                        'is_allowed' => in_array($menu['key'], $allowed),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'บันทึกสิทธิ์สำเร็จ');
    }
}