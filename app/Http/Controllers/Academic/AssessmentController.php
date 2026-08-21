<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\StudentAssessment;
use App\Models\Academic\Level;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::with('academicYear')->orderByDesc('semester_id')->get();

        $semesterId = $request->get('semester_id')
            ?? optional(Semester::current())->semester_id
            ?? optional($semesters->first())->semester_id;

        // ตั้งชื่อ classLevels (ไม่ใช่ levels เฉยๆ) เพราะหน้านี้มีตัวแปร $levels ที่ใช้ชื่ออยู่แล้ว
        // (เป็น scale ให้คะแนน ดีเยี่ยม/ดี/ผ่าน/ไม่ผ่าน ไม่เกี่ยวกับระดับชั้นเรียนเลย) ชนกันแล้วจะเงียบๆ ทับกัน
        $classLevels = Level::orderBy('sort_order')->get();
        $levelId = $request->get('level_id');

        $sections = ClassSection::real()->with('level')
            ->where('semester_id', $semesterId)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->get()
            ->sortBy(fn($s) => [$s->level?->sort_order ?? 99, $s->section_number])
            ->values();

        $sectionId = $request->get('section_id');
        $students    = collect();
        $assessments = collect();

        if ($sectionId) {
            $students = StudentSection::where('student_sections.section_id', $sectionId)
                ->join('students', 'student_sections.student_id', '=', 'students.student_id')
                ->orderBy('student_sections.student_number')
                ->select('students.*', 'student_sections.student_number')
                ->get();

            $assessments = StudentAssessment::where('semester_id', $semesterId)
                ->whereIn('student_id', $students->pluck('student_id'))
                ->get()
                ->keyBy('student_id');
        }

        return view('academic.assessments_index', compact(
            'semesters', 'semesterId', 'classLevels', 'levelId', 'sections', 'sectionId', 'students', 'assessments'
        ));
    }

    public function save(Request $request)
    {
        $semesterId = $request->input('semester_id');
        $sectionId  = $request->input('section_id');
        $rows       = $request->input('assess', []);

        foreach ($rows as $studentId => $vals) {
            StudentAssessment::updateOrCreate(
                ['student_id' => $studentId, 'semester_id' => $semesterId],
                [
                    'reading_thinking' => $vals['reading']  ?: null,
                    'desired_char'     => $vals['char']     ?: null,
                    'activity'         => $vals['activity'] ?: null,
                ]
            );
        }

        return redirect()
            ->route('assessments.index', ['semester_id' => $semesterId, 'section_id' => $sectionId])
            ->with('success', 'บันทึกผลการประเมินสำเร็จ');
    }
}
