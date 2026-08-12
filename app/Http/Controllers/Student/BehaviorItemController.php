<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\BehaviorItem;
use Illuminate\Http\Request;

class BehaviorItemController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'deduct');
        if (!in_array($type, ['add', 'deduct'], true)) {
            $type = 'deduct';
        }

        $query = BehaviorItem::where('type', $type)->orderBy('sort_order')->orderBy('behavior_item_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_th', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('student.behavior_item_index', compact('items', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:add,deduct',
            'name_th' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'points' => 'required|numeric|min:0|max:999.99',
        ]);

        BehaviorItem::create([
            'type' => $request->type,
            'name_th' => $request->name_th,
            'name_en' => $request->name_en,
            'points' => $request->points,
            'sort_order' => (int) BehaviorItem::where('type', $request->type)->max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'เพิ่มรายการพฤติกรรมสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'points' => 'required|numeric|min:0|max:999.99',
        ]);

        BehaviorItem::findOrFail($id)->update([
            'name_th' => $request->name_th,
            'name_en' => $request->name_en,
            'points' => $request->points,
        ]);

        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }

    public function destroy($id)
    {
        BehaviorItem::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'ลบสำเร็จ');
    }
}
