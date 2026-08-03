<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Academic\ClassSection;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('curriculums')->orderBy('name')->get();
        return view('academic.programs', compact('programs'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        Program::create($request->only(['name', 'description']));
        return redirect()->back()->with('success', 'เพิ่มหลักสูตรสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required']);
        Program::findOrFail($id)->update($request->only(['name', 'description']));
        return redirect()->back()->with('success', 'แก้ไขหลักสูตรสำเร็จ');
    }

    public function destroy($id)
    {
        $program = Program::withCount('curriculums')->findOrFail($id);
        if ($program->curriculums_count > 0) {
            return redirect()->back()->with('error', "ลบไม่ได้ หลักสูตรนี้ยังมีแผนอยู่ {$program->curriculums_count} แผน");
        }
        $program->delete();
        return redirect()->back()->with('success', 'ลบหลักสูตรสำเร็จ');
    }

    public function plans($id)
    {
        $program = Program::findOrFail($id);
        $curriculums = $program->curriculums()->with('level')->orderBy('level_id')->get();

        $curriculumIds = $curriculums->pluck('curriculum_id');
        $sectionsByCurriculum = ClassSection::with('level')
            ->whereIn('curriculum_id', $curriculumIds)
            ->get()
            ->groupBy('curriculum_id');

        return view('academic.program_plans', compact('program', 'curriculums', 'sectionsByCurriculum'));
    }
}
