<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Personne\Position;
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
}
