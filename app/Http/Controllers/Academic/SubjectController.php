<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;


class SubjectController extends Controller
{

    public function index(Request $request)
    {
        $query = Subject::query();
        if ($request->filled('group')) $query->where('subject_group', $request->group);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('code', 'like', "%$s%")->orWhere('name_th', 'like', "%$s%");
            });
        }
        $subjects = $query->orderBy('code')->paginate(30);
        $groups = Subject::select('subject_group')->distinct()->whereNotNull('subject_group')->pluck('subject_group');
        return view('academic.subjects', compact('subjects', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate(['code' => 'required|unique:subjects,code', 'name_th' => 'required', 'credits' => 'required|numeric']);
        Subject::create($request->only(['code', 'name_th', 'name_short', 'code_en', 'name_en', 'subject_group', 'subject_type', 'credits', 'hours_per_week', 'hours_per_year']));
        return redirect()->back()->with('success', 'เพิ่มรายวิชาสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $request->validate(['code' => 'required|unique:subjects,code,' . $id . ',subject_id', 'name_th' => 'required']);
        $subject->update($request->only(['code', 'name_th', 'name_short', 'code_en', 'name_en', 'subject_group', 'subject_type', 'credits', 'hours_per_week', 'hours_per_year']));
        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }


   public function toggle($id)
    {
        $s = Subject::findOrFail($id);
        $s->update(['is_active' => !$s->is_active]);
        return redirect()->back();
    }

    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบสำเร็จ');
    }
}