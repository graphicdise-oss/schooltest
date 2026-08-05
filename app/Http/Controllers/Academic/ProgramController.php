<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Academic\Curriculum;
use App\Models\Academic\Level;
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

    // ขั้นแรกของหน้า "แผน" ของหลักสูตร — โชว์แค่ "ชั้นเรียน" ที่มีแผนอยู่แล้ว (ม.4, ม.5, ม.6, ...)
    // ไม่โชว์แผนแต่ละห้อง (เช่น ม.4/1, ม.4/2) ปนกันตรงนี้ — ต้องกดเข้าไปดูทีละชั้นก่อน
    public function plans($id)
    {
        $program = Program::findOrFail($id);

        $curriculums = $program->curriculums()->with('level')->get();

        $levelGroups = $curriculums
            ->groupBy('level_id')
            ->map(function ($group) {
                return (object) [
                    'level_id' => $group->first()->level_id,
                    'level'    => $group->first()->level,
                    'count'    => $group->count(),
                    'years'    => $group->pluck('year_applied')->filter()->unique()->sortDesc()->values(),
                ];
            })
            ->sortBy(fn ($g) => $g->level->sort_order ?? 999)
            ->values();

        // สำหรับป๊อปอัพ "เพิ่มแผน" ในหน้านี้ — ยังไม่รู้ว่าจะเพิ่มแผนให้ชั้นไหน เลยต้องมี dropdown เลือกชั้นในป๊อปอัพเอง
        $levels = Level::orderBy('sort_order')->get();
        $currentYearName = AcademicYear::where('is_current', true)->value('year_name');

        return view('academic.program_levels', compact('program', 'levelGroups', 'levels', 'currentYearName'));
    }

    // แผนของ "ชั้นเรียนเดียว" ในหลักสูตรนี้ (เช่น ม.4 -> ม.4/1, ม.4/2, ... ทุกปีการศึกษา) — กดเข้ามาจากหน้ารายการชั้นเรียน
    public function levelPlans($id, $levelId)
    {
        $program = Program::findOrFail($id);
        $level   = Level::findOrFail($levelId);

        $curriculums = $program->curriculums()->with('level')
            ->where('level_id', $levelId)
            ->orderByDesc('year_applied')->orderBy('name')->get();

        $currentYearName = AcademicYear::where('is_current', true)->value('year_name');

        return view('academic.program_plans', compact('program', 'level', 'curriculums', 'currentYearName'));
    }
}
