<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student\StudentType;
use Illuminate\Http\Request;

class StudentTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentType::orderBy('sort_order')->orderBy('type_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_th', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $types = $query->paginate(20)->withQueryString();

        return view('student.student_type_index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_th'    => 'required|string|max:100',
            'name_en'    => 'nullable|string|max:100',
            'caretaker'  => 'nullable|string|max:100',
        ]);

        StudentType::create([
            'name_th'    => $request->name_th,
            'name_en'    => $request->name_en,
            'caretaker'  => $request->caretaker,
            'is_active'  => true,
            'sort_order' => StudentType::max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'เพิ่มประเภทนักเรียนสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_th'    => 'required|string|max:100',
            'name_en'    => 'nullable|string|max:100',
            'caretaker'  => 'nullable|string|max:100',
        ]);

        StudentType::findOrFail($id)->update([
            'name_th'   => $request->name_th,
            'name_en'   => $request->name_en,
            'caretaker' => $request->caretaker,
        ]);

        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }

    public function toggle($id)
    {
        $type = StudentType::findOrFail($id);
        $type->update(['is_active' => !$type->is_active]);
        return redirect()->back();
    }

    public function destroy($id)
    {
        StudentType::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบสำเร็จ');
    }
}
