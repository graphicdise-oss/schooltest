<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\TeachingAssign;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
  public function printTranscript(Request $request, $studentId)
{
    $student = Student::findOrFail($studentId);
    [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);

    // ==========================================
    // จัดการข้อมูลตามเงื่อนไขที่ส่งมาจาก Modal
    // ==========================================

    // 1. กรองเฉพาะเทอมที่ติ๊กเลือกเอาไว้
    if ($request->has('selected_semesters')) {
        $selectedKeys = $request->input('selected_semesters');
        $grades = $grades->filter(function($value, $key) use ($selectedKeys) {
            return in_array($key, $selectedKeys);
        });
    }

    // 2. ถ้าติ๊ก "ไม่คำนวณ/ไม่แสดงเกรดภาคเรียนสุดท้าย" ให้ลบข้อมูลเทอมล่าสุดทิ้ง
    if ($request->boolean('hide_last_semester') && $grades->count() > 0) {
        $lastKey = $grades->keys()->last();
        $grades->forget($lastKey);
    }

    // 3. คำนวณ GPA และหน่วยกิตสะสมใหม่ (หลังจากการกรองเทอมด้านบนแล้ว)
    $totalCredits = 0;
    $totalPoints  = 0;
    foreach ($grades as $semGrades) {
        foreach ($semGrades as $g) {
            $credits = $g->teachingAssign->subject->credits ?? 0;
            $totalCredits += $credits;
            $totalPoints  += ($g->gpa_point ?? 0) * $credits;
        }
    }
    $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

    // 4. รวบรวม option อื่นๆ เพื่อส่งไปจัดการซ่อน/แสดง ในหน้ากระดาษ (Blade)
    $options = [
        'show_original' => $request->boolean('show_original'),
        'hide_profile'  => $request->boolean('hide_profile'),
        'english_report'=> $request->boolean('english_report'),
    ];

    return Pdf::loadView('academic.transcript_print', compact('student', 'grades', 'gpa', 'totalCredits', 'options'))
        ->stream("transcript_{$student->student_code}.pdf");
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

        $manualGrade = $request->grade !== '' ? $request->grade : null;
        $gradeMap = ['4'=>4.0,'3.5'=>3.5,'3'=>3.0,'2.5'=>2.5,'2'=>2.0,'1.5'=>1.5,'1'=>1.0,'0'=>0.0,'I'=>0.0,'W'=>0.0,'S'=>4.0,'U'=>0.0];
        $finalGrade    = $manualGrade ?? $gradeInfo['grade'];
        $finalGpaPoint = $manualGrade !== null ? ($gradeMap[$manualGrade] ?? $gradeInfo['gpa']) : $gradeInfo['gpa'];

        $grade->update([
            'total_score' => $score,
            'grade'       => $finalGrade,
            'gpa_point'   => $finalGpaPoint,
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
        $allGrades = FinalGrade::with([
                'teachingAssign.subject',
                'teachingAssign.classSection.level',
                'semester.academicYear',
            ])
            ->where('student_id', $studentId)
            ->orderBy('semester_id')
            ->get();

        $grades = $allGrades->groupBy(fn($g) =>
            ($g->semester->academicYear->year_name ?? '') . '|' .
            ($g->teachingAssign->classSection->level->name ?? '') . '|' .
            ($g->semester->semester_name ?? '')
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

        return Pdf::loadView('academic.grade_print', compact('assign', 'students', 'categories', 'scoreMatrix', 'finalGrades'))
            ->setPaper('a4', 'landscape')
            ->stream("scores_{$assign->subject->code}.pdf");
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
    
   // ==========================================
    // พิมพ์ใบ ปพ.1 (และกรองเทอมตามที่ติ๊กเลือก)
    // ==========================================
    public function printPor1(Request $request, $studentId)
    {
        // 1. ดึงข้อมูล
        $student = Student::with('education')->findOrFail($studentId);
        $father = \App\Models\StudentFamily::where('student_id', $studentId)->where('guardian_type', 'บิดา')->first();
        $mother = \App\Models\StudentFamily::where('student_id', $studentId)->where('guardian_type', 'มารดา')->first();
        
        $docNumber = StudentDocNumber::where('student_id', $studentId)->latest()->first()
            ?? (object)['doc_set' => '', 'doc_number' => ''];

        // 2. รับค่าเทอมที่ติ๊กมาจาก Modal
        $filterActive = $request->has('filter_active'); // เช็คว่าส่งมาจากหน้าป๊อปอัปหรือไม่
        $selectedSemesters = $request->input('semesters', []); 
        
        // 3. ดึงเกรดทั้งหมด
        $allGrades = FinalGrade::with(['teachingAssign.subject', 'teachingAssign.classSection.level', 'semester.academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('semester_id')
            ->get();
            
        $yearGroups = [];
        
        $shownSemesterIds = [];

        foreach ($allGrades as $grade) {
            $year = $grade->semester->academicYear->year_name ?? '';
            $term = $grade->semester->semester_name ?? '';
            $level = $grade->teachingAssign->classSection->level->name ?? '';

            $semKey = $year . '/' . $term;

            // 🛑 ถ้าเปิดใช้งานตัวกรอง (ผ่าน Modal) และเทอมนี้ไม่อยู่ในรายการที่ติ๊ก -> ข้ามทันที!
            if ($filterActive && !in_array($semKey, $selectedSemesters)) {
                continue;
            }

            $shownSemesterIds[] = $grade->semester_id;

            if (!isset($yearGroups[$year])) {
                $yearGroups[$year] = ['year' => $year, 'level' => $level, 'semesters' => []];
            }
            if (!isset($yearGroups[$year]['semesters'][$term])) {
                $yearGroups[$year]['semesters'][$term] = [];
            }
            
            $yearGroups[$year]['semesters'][$term][] = $grade;
        }

        // วันอนุมัติการจบ
        $studentSectionIds = \App\Models\Academic\StudentSection::where('student_id', $studentId)->pluck('section_id');
        $pp2Setting = \App\Models\Pp2SectionSetting::whereIn('section_id', $studentSectionIds)->first();
        $approveDate = '';
        if ($request->filled('approve_date')) {
            $approveDate = $this->formatThaiDate($request->input('approve_date'));
        } elseif ($pp2Setting?->issued_date) {
            $approveDate = $this->formatThaiDate($pp2Setting->issued_date->format('Y-m-d'));
        }

        // วันออกจากโรงเรียน และ สาเหตุ
        $promotion = \App\Models\Academic\Promotion::where('student_id', $studentId)->latest('promo_date')->first();
        $leaveDate   = $promotion?->promo_date ? $this->formatThaiDate($promotion->promo_date->format('Y-m-d')) : '';
        $leaveReason = $promotion?->remark ?? '';

        $signSettings = \App\Models\Academic\Pp2Setting::getInstance();
        $school = config('school');
        if ($signSettings->registrar_name) $school['registrar_name'] = $signSettings->registrar_name;
        if ($signSettings->director_name)  $school['director_name']  = $signSettings->director_name;

        // ผลการประเมิน (อ่าน/คุณลักษณะ/กิจกรรม) ของภาคเรียนล่าสุดที่แสดงในเอกสาร
        $latestSemId = !empty($shownSemesterIds) ? max($shownSemesterIds) : null;
        $assessment = \App\Models\Academic\StudentAssessment::where('student_id', $studentId)
                ->when($latestSemId, fn($q) => $q->where('semester_id', $latestSemId))
                ->first()
            ?? \App\Models\Academic\StudentAssessment::where('student_id', $studentId)
                ->orderByDesc('semester_id')->first();

        // คะแนน O-NET (ของปีการศึกษาล่าสุดที่นักเรียนสอบ)
        $onetScores = \App\Models\Academic\OnetScore::where('student_id', $studentId)
            ->orderByDesc('year_id')
            ->get();
        $onetYearId = $onetScores->first()?->year_id;
        $onetScores = $onetScores->where('year_id', $onetYearId)->keyBy('subject');

        return Pdf::loadView('academic.por1_print', compact('student', 'father', 'mother', 'yearGroups', 'docNumber', 'approveDate', 'leaveDate', 'leaveReason', 'school', 'assessment', 'onetScores'))
            ->stream("por1_{$student->student_code}.pdf");
    }

    private function formatThaiDate(string $dateStr): string
    {
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                   'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $d = \Carbon\Carbon::parse($dateStr);
        return $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
    }
}