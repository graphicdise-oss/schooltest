<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Personne\PersonnelType;
use App\Models\Personne\PersonnelTypePermission;
use App\Support\MenuPermissionCatalog;
use Illuminate\Http\Request;

class PersonnelTypeController extends Controller
{
    // เดิมกำหนดสิทธิ์ตรงนี้ (ตามประเภทบุคลากร) — ย้ายไปกำหนดตาม "ตำแหน่ง" แทนแล้ว (ดู PositionController)
    // เมธอด/route/view หน้ากำหนดสิทธิ์ในไฟล์นี้ไม่ได้ผูกกับการเช็คสิทธิ์จริงอีกต่อไป (User::allowedMenuKeys()
    // เช็คผ่าน Position แล้ว) เหลือไว้เฉยๆ ไม่ได้ลบเผื่ออยากดูค่าที่เคยตั้งไว้ ไม่มีลิงก์เข้าถึงจากหน้าเว็บแล้ว
    private function getMenuList()
    {
        return MenuPermissionCatalog::list();
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