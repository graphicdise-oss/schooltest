<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Personne\Position;
use App\Models\Personne\PositionPermission;
use App\Support\MenuPermissionCatalog;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::orderBy('sort_order')->orderBy('position_id');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $positions = $query->paginate(20)->withQueryString();

        return view('settings.position_index', compact('positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'employee_type' => 'required|string|max:50',
        ]);

        Position::create([
            'name'          => $request->name,
            'employee_type' => $request->employee_type,
            'is_active'     => true,
            'sort_order'    => Position::max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'เพิ่มตำแหน่งสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'employee_type' => 'required|string|max:50',
        ]);

        Position::findOrFail($id)->update([
            'name'          => $request->name,
            'employee_type' => $request->employee_type,
        ]);

        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }

    public function toggle($id)
    {
        $pos = Position::findOrFail($id);
        $pos->update(['is_active' => !$pos->is_active]);
        return redirect()->back();
    }

    public function destroy($id)
    {
        Position::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบสำเร็จ');
    }

    // ===== หน้ากำหนดสิทธิ์ (ย้ายมาจากประเภทบุคลากร — ดู PersonnelTypeController สำหรับกลไกเดิม) =====
    public function permissions($id)
    {
        $position = Position::with('permissions')->findOrFail($id);
        $menuList = MenuPermissionCatalog::list();

        $existingPerms = $position->permissions->keyBy('menu_key');

        return view('settings.position_permissions', compact('position', 'menuList', 'existingPerms'));
    }

    public function savePermissions(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        $menuList = MenuPermissionCatalog::list();
        $allowed = $request->input('permissions', []);

        foreach ($menuList as $group => $menus) {
            foreach ($menus as $menu) {
                PositionPermission::updateOrCreate(
                    ['position_id' => $position->position_id, 'menu_key' => $menu['key']],
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
