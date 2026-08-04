<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Academic\Curriculum;
use App\Models\Academic\ClassSection;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        // แสดงแผนทุกปีรวมกัน ไม่กรองตามปีการศึกษา — เพราะ "ปีการศึกษา" ของแผนเป็นข้อความ
        // ที่พิมพ์เอง พอเลือกปีบนตัวกรองไม่ตรงกับที่พิมพ์ไว้เป๊ะๆ แผนจะหายไปจากลิสต์แบบไม่รู้สาเหตุ
        $programs = Program::withCount('curriculums')->orderBy('name')->get();

        // แผนเก่าที่มีอยู่ก่อนฟีเจอร์หลักสูตรนี้ (หรือแผนที่ยังไม่ได้ผูกหลักสูตร) จะไม่โผล่ในลิสต์ด้านบน
        // เพราะยังไม่มี program_id — เก็บจำนวนไว้เตือน กันข้อมูลดูเหมือนหายไปทั้งที่ยังอยู่ในระบบ
        $unassignedCount = Curriculum::whereNull('program_id')->count();

        return view('academic.programs', compact('programs', 'unassignedCount'));
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

        // แสดงแผนของหลักสูตรนี้ "ทุกปี" รวมกัน (ไม่กรองปี กันแผนหายจากลิสต์แบบงงๆ)
        // เรียงปีล่าสุดก่อน แล้วค่อยตามระดับชั้น
        $curriculums = $program->curriculums()->with('level')
            ->orderByDesc('year_applied')->orderBy('level_id')->get();

        $curriculumIds = $curriculums->pluck('curriculum_id');
        $sectionsByCurriculum = ClassSection::with('level')
            ->whereIn('curriculum_id', $curriculumIds)
            ->get()
            ->groupBy('curriculum_id');

        return view('academic.program_plans', compact('program', 'curriculums', 'sectionsByCurriculum'));
    }
}
