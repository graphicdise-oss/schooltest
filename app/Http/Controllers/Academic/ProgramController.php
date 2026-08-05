<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Academic\Curriculum;
use App\Models\Academic\ClassSection;
use App\Models\Academic\AcademicYear;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::with('semesters')->orderBy('year_name', 'desc')->get();

        // ไม่มีโหมด "ดูทุกปี" แล้ว ต้องมีปีที่เลือกอยู่เสมอ — ค่าเริ่มต้นคือปีการศึกษาปัจจุบัน
        // (ถ้ายังไม่เคยตั้งปีปัจจุบันไว้เลย ใช้ปีล่าสุดในลิสต์แทน, ถ้ายังไม่มีปีในระบบเลยก็ปล่อยว่าง)
        $currentYearId = $request->year_id;
        if ($currentYearId === null) {
            $currentYearId = $academicYears->where('is_current', true)->first()->year_id
                ?? $academicYears->first()->year_id
                ?? null;
        }
        $selectedYear = $currentYearId !== null ? $academicYears->where('year_id', $currentYearId)->first() : null;

        // จำนวน "แผน" ต่อหลักสูตร: ถ้าเลือกปีไว้ ให้นับเฉพาะแผนของปีนั้น (กรองจากคอลัมน์ year_applied
        // ให้ตรงกับชื่อปีการศึกษา เช่น 2568) ไม่งั้นนับรวมทุกปีเหมือนเดิม
        $programs = Program::withCount(['curriculums' => function ($q) use ($selectedYear) {
            if ($selectedYear) {
                $q->where('year_applied', $selectedYear->year_name);
            }
        }])->orderBy('name')->get();

        // แผนเก่าที่มีอยู่ก่อนฟีเจอร์หลักสูตรนี้ (หรือแผนที่ยังไม่ได้ผูกหลักสูตร) จะไม่โผล่ในลิสต์ด้านบน
        // เพราะยังไม่มี program_id — เก็บจำนวนไว้เตือน กันข้อมูลดูเหมือนหายไปทั้งที่ยังอยู่ในระบบ
        $unassignedCount = Curriculum::whereNull('program_id')->count();

        return view('academic.programs', compact(
            'programs', 'unassignedCount', 'academicYears', 'currentYearId', 'selectedYear'
        ));
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
