<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\TeachingAssign;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    // หน้ารวมผลการเรียน (เลือกเทอม + ห้อง)
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();
        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')->get();

        return view('academic.grades_index', compact('semesters', 'sections', 'semesterId'));
    }

    // ผลการเรียนรายคน (Transcript)
    public function studentTranscript($studentId)
    {
        $student = Student::findOrFail($studentId);
        [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);
        return view('academic.transcript', compact('student', 'grades', 'gpa', 'totalCredits'));
    }

    // พิมพ์ใบ Transcript รายคน
    public function printTranscript($studentId)
    {
        $student = Student::findOrFail($studentId);
        [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);
        return view('academic.transcript_print', compact('student', 'grades', 'gpa', 'totalCredits'));
    }

    // หน้าแก้ไขเกรดรายคน
    public function editStudentGrades($studentId)
    {
        $student = Student::findOrFail($studentId);
        [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);
        return view('academic.grades_edit', compact('student', 'grades', 'gpa', 'totalCredits'));
    }

    // อัปเดตเกรด
    public function updateGrade(Request $request, $gradeId)
    {
        $grade = FinalGrade::findOrFail($gradeId);
        $score = $request->total_score;
        $gradeInfo = FinalGrade::calculateGrade($score);
        $grade->update([
            'total_score' => $score,
            'grade'       => $request->grade ?? $gradeInfo['grade'],
            'gpa_point'   => $gradeInfo['gpa'],
            'remark'      => $score >= 50 ? 'ผ่าน' : 'ไม่ผ่าน',
        ]);
        return redirect()->back()->with('success', 'แก้ไขเกรดสำเร็จ');
    }

    // ลบเกรด
    public function destroyGrade($gradeId)
    {
        FinalGrade::findOrFail($gradeId)->delete();
        return redirect()->back()->with('success', 'ลบเกรดสำเร็จ');
    }

    // Export score sheet to Excel
    public function exportScoreExcel($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level',
            'classSection.semester.academicYear', 'scoreCategories'])->findOrFail($assignId);

        $students = \App\Models\Academic\StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')->get();

        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        $finalGrades = FinalGrade::where('assign_id', $assignId)->get()->keyBy('student_id');

        $filename = 'คะแนน_' . $assign->subject->code . '_' . $assign->classSection->level->name . $assign->classSection->section_number . '.xls';

        $html = view('academic.score_excel', compact('assign', 'students', 'categories', 'scoreMatrix', 'finalGrades'))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . urlencode($filename) . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    private function buildTranscriptData($studentId): array
    {
        $allGrades = FinalGrade::with(['teachingAssign.subject', 'semester.academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('semester_id')
            ->get();

        $grades = $allGrades->groupBy(fn($g) =>
            ($g->semester->academicYear->year_name ?? '') . '|' . ($g->semester->semester_name ?? '')
        );

        $totalCredits = 0;
        $totalPoints  = 0;
        foreach ($allGrades as $g) {
            $credits = $g->teachingAssign->subject->credits ?? 0;
            $totalCredits += $credits;
            $totalPoints  += ($g->gpa_point ?? 0) * $credits;
        }
        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

        return [$grades, $gpa, $totalCredits];
    }

    // ผลการเรียนรายห้อง
    public function sectionReport($sectionId)
    {
        $section = ClassSection::with(['level', 'semester.academicYear'])->findOrFail($sectionId);

        $students = StudentSection::with('student')
            ->where('section_id', $sectionId)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $grades = FinalGrade::with('teachingAssign.subject')
            ->whereHas('teachingAssign', fn($q) => $q->where('section_id', $sectionId))
            ->where('semester_id', $section->semester_id)
            ->get()
            ->groupBy('student_id');

        return view('academic.section_grades', compact('section', 'students', 'grades'));
    }

    // พิมพ์ใบบันทึกคะแนน
    public function printScoreSheet($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level',
            'classSection.semester.academicYear', 'scoreCategories'])->findOrFail($assignId);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')->get();

        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        $finalGrades = FinalGrade::where('assign_id', $assignId)
            ->get()->keyBy('student_id');

        return view('academic.grade_print', compact('assign', 'students', 'categories', 'scoreMatrix', 'finalGrades'));
    }

    // รายงาน GPA
    public function gpaReport(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();

        $gpaData = DB::table('final_grades as fg')
            ->join('teaching_assigns as ta', 'fg.assign_id', '=', 'ta.assign_id')
            ->join('subjects as sub', 'ta.subject_id', '=', 'sub.subject_id')
            ->join('students as s', 'fg.student_id', '=', 's.student_id')
            ->join('student_sections as ss', function($j) {
                $j->on('ss.student_id', '=', 's.student_id')
                  ->on('ss.section_id', '=', 'ta.section_id');
            })
            ->where('fg.semester_id', $semesterId)
            ->select(
                's.student_id', 's.student_code', 's.thai_firstname', 's.thai_lastname',
                'ss.student_number',
                DB::raw('ROUND(SUM(fg.gpa_point * sub.credits) / NULLIF(SUM(sub.credits), 0), 2) as gpa'),
                DB::raw('SUM(sub.credits) as total_credits')
            )
            ->groupBy('s.student_id', 's.student_code', 's.thai_firstname', 's.thai_lastname', 'ss.student_number')
            ->orderBy('gpa', 'desc')
            ->get();

        return view('academic.gpa_report', compact('gpaData', 'semesters', 'semesterId'));
    }
}