<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Program;
use App\Models\Academic\ClassSection;
use App\Models\Academic\AcademicYear;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    // เลือกปีการศึกษาที่กำลังดูอยู่ (จาก query string, ถ้าไม่ระบุใช้ปีปัจจุบัน)
    private function resolveYear(Request $request): array
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();
        $currentYearId = $request->year_id ?: (AcademicYear::current()->year_id ?? optional($academicYears->first())->year_id);
        $selectedYear = $academicYears->firstWhere('year_id', $currentYearId);
        return [$academicYears, $currentYearId, $selectedYear];
    }

    public function index(Request $request)
    {
        [$academicYears, $currentYearId, $selectedYear] = $this->resolveYear($request);
        $yearName = $selectedYear->year_name ?? null;

        $programs = Program::withCount(['curriculums' => function ($q) use ($yearName) {
            $q->when($yearName, fn ($q2) => $q2->where('year_applied', $yearName));
        }])->orderBy('name')->get();

        return view('academic.programs', compact('programs', 'academicYears', 'currentYearId', 'yearName'));
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

    public function plans($id, Request $request)
    {
        $program = Program::findOrFail($id);
        [$academicYears, $currentYearId, $selectedYear] = $this->resolveYear($request);
        $yearName = $selectedYear->year_name ?? null;

        $curriculums = $program->curriculums()
            ->when($yearName, fn ($q) => $q->where('year_applied', $yearName))
            ->with('level')->orderBy('level_id')->get();

        $curriculumIds = $curriculums->pluck('curriculum_id');
        $sectionsByCurriculum = ClassSection::with('level')
            ->whereIn('curriculum_id', $curriculumIds)
            ->get()
            ->groupBy('curriculum_id');

        return view('academic.program_plans', compact('program', 'curriculums', 'sectionsByCurriculum', 'academicYears', 'currentYearId', 'yearName'));
    }
}
