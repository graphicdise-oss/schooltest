<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $departments = Department::with('head')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id')
            ->get();

        $personnels = Personnel::orderBy('thai_firstname')->get();

        return view('settings.department_index', compact('departments', 'personnels', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments,name',
        ], [
            'name.required' => 'กรุณาระบุชื่อแผนก',
            'name.unique'   => 'ชื่อแผนกนี้มีอยู่แล้ว',
        ]);

        Department::create([
            'name'    => $request->name,
            'head_id' => $request->head_id ?: null,
        ]);

        return back()->with('success', 'เพิ่มแผนกสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $dept = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:departments,name,' . $id,
        ], [
            'name.required' => 'กรุณาระบุชื่อแผนก',
            'name.unique'   => 'ชื่อแผนกนี้มีอยู่แล้ว',
        ]);

        $dept->update([
            'name'    => $request->name,
            'head_id' => $request->head_id ?: null,
        ]);

        return back()->with('success', 'แก้ไขแผนกสำเร็จ');
    }

    public function destroy($id)
    {
        Department::findOrFail($id)->delete();
        return back()->with('success', 'ลบแผนกสำเร็จ');
    }
}