<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\StudentAssessment;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\Pp2Setting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class Por6Controller extends Controller
{
    // เลือกภาคเรียน/ระดับชั้น/ห้องเรียน
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $levelId    = $request->level_id;
        $sectionId  = $request->section_id;

        $semesters = Semester::with('academicYear')->orderByDesc('semester_id')->get();
        $levels    = Level::orderBy('sort_order')->get();

        $sections = collect();
        if ($semesterId) {
            $sections = ClassSection::with('level')
                ->where('semester_id', $semesterId)
                ->when($levelId, fn($q) => $q->where('level_id', $levelId))
                ->orderBy('section_number')
                ->get();
        }

        $students = collect();
        if ($sectionId) {
            $students = StudentSection::with('student')
                ->where('section_id', $sectionId)
                ->where('status', 'กำลังศึกษา')
                ->orderBy('student_number')
                ->get();
        }

        return view('academic.por6_index', compact(
            'semesters', 'semesterId', 'levels', 'levelId', 'sections', 'sectionId', 'students'
        ));
    }

    // พิมพ์ทั้งห้อง
    public function printSection($sectionId)
    {
        return $this->renderPrint($sectionId, null);
    }

    // พิมพ์รายบุคคล
    public function printStudent($sectionId, $studentId)
    {
        return $this->renderPrint($sectionId, $studentId);
    }

    private function renderPrint($sectionId, $studentId)
    {
        $section  = ClassSection::with(['level', 'semester.academicYear', 'homeroomTeacher'])->findOrFail($sectionId);
        $semester = $section->semester;

        $query = StudentSection::with('student')
            ->where('section_id', $sectionId)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number');
        if ($studentId) $query->where('student_id', $studentId);
        $studentSections = $query->get();

        $signSettings = Pp2Setting::getInstance();
        $school = config('school');
        if ($signSettings->director_name) $school['director_name'] = $signSettings->director_name;

        $reportData = $studentSections->map(function ($ss) use ($semester) {
            $grades = FinalGrade::with('teachingAssign.subject')
                ->where('student_id', $ss->student_id)
                ->where('semester_id', $semester->semester_id)
                ->get();

            $rows = $grades->map(function ($g) {
                $subj       = $g->teachingAssign->subject ?? null;
                $isActivity = ($subj->subject_group ?? '') === 'กิจกรรมพัฒนาผู้เรียน';
                $type       = $isActivity ? 'กิจกรรม' : ($subj->subject_type ?? '-');
                return (object) [
                    'code'       => $subj->code ?? '-',
                    'name'       => $subj->name_th ?? '-',
                    'type'       => $type,
                    'credits'    => (float) ($subj->credits ?? 0),
                    'grade'      => $g->grade,
                    'gpa_point'  => (float) ($g->gpa_point ?? 0),
                    'is_activity'=> $isActivity,
                    'sort'       => $isActivity ? 3 : ($type === 'เพิ่มเติม' ? 2 : 1),
                ];
            })->sortBy('sort')->values();

            $basicCredits = $rows->where('type', 'พื้นฐาน')->sum('credits');
            $extraCredits = $rows->where('type', 'เพิ่มเติม')->sum('credits');
            $totalCredits = $basicCredits + $extraCredits;

            $creditSum = 0.0; $pointSum = 0.0;
            foreach ($rows as $r) {
                if ($r->is_activity) continue;
                $creditSum += $r->credits;
                $pointSum  += $r->credits * $r->gpa_point;
            }
            $gpa = $creditSum > 0 ? round($pointSum / $creditSum, 2) : 0;

            $assessment = StudentAssessment::where('student_id', $ss->student_id)
                ->where('semester_id', $semester->semester_id)
                ->first();

            return (object) [
                'studentSection' => $ss,
                'student'        => $ss->student,
                'rows'           => $rows,
                'basicCredits'   => $basicCredits,
                'extraCredits'   => $extraCredits,
                'totalCredits'   => $totalCredits,
                'gpa'            => $gpa,
                'assessment'     => $assessment,
            ];
        });

        $filename = $studentId
            ? "por6_{$section->level->name}-{$section->section_number}_{$studentId}.pdf"
            : "por6_{$section->level->name}-{$section->section_number}.pdf";

        return Pdf::loadView('academic.por6_print', compact('section', 'semester', 'reportData', 'school'))
            ->stream($filename);
    }
}
