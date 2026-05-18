<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\FinalGrade;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class PorPor3Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $currentSem    = Semester::where('is_current', true)->with('academicYear')->first();

        $yearId    = $request->year_id   ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $term      = $request->term      ?? ($currentSem->semester_name ?? '1');
        $levelId   = $request->level_id;
        $sectionId = $request->section_id;
        $search    = trim($request->search ?? '');

        $semester   = Semester::where('year_id', $yearId)->where('semester_name', $term)->first();
        $semesterId = $semester?->semester_id;

        $levels = Level::whereHas('classSections', fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('sort_order')->get();

        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        $currentSection = null;
        if ($sectionId && $sectionId !== 'all') {
            $currentSection = ClassSection::with('level')->find($sectionId);
        }

        $query = StudentSection::with(['student', 'classSection.level'])
            ->whereHas('classSection', fn($q) => $q->where('semester_id', $semesterId));

        if ($levelId) {
            $query->whereHas('classSection', fn($q) => $q->where('level_id', $levelId));
        }
        if ($sectionId && $sectionId !== 'all') {
            $query->where('section_id', $sectionId);
        }
        if ($search !== '') {
            $query->whereHas('student', fn($q) => $q
                ->where('thai_firstname', 'like', "%{$search}%")
                ->orWhere('thai_lastname', 'like', "%{$search}%")
                ->orWhere('student_code', 'like', "%{$search}%")
            );
        }

        $rows = $query->orderBy('student_number')->get();

        $students = $rows->map(fn($ss) => [
            'student' => $ss->student,
            'section' => $ss->classSection,
        ])->filter(fn($r) => $r['student'])->values();

        $personnels = Personnel::where('status', 'ปฏิบัติงาน')
            ->orderBy('thai_firstname')
            ->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname', 'position']);

        $savedApproverId  = session('por3_approver_id');
        $savedApproveDate = session('por3_approve_date');
        $savedApprover    = $savedApproverId ? $personnels->firstWhere('personnel_id', $savedApproverId) : null;

        return view('academic.por3_index', compact(
            'academicYears', 'levels', 'sections', 'students', 'personnels',
            'yearId', 'term', 'levelId', 'sectionId', 'search',
            'semesterId', 'currentSection',
            'savedApproverId', 'savedApproveDate', 'savedApprover'
        ));
    }

    public function savePrintSettings(Request $request)
    {
        session([
            'por3_approver_id'   => $request->approver_id,
            'por3_approve_date'  => $request->approve_date,
        ]);
        return redirect()->back()->with('settings_saved', true);
    }

    public function print(Request $request)
    {
        $sectionId   = $request->section_id;
        $approverId  = $request->approver_id  ?? session('por3_approver_id');
        $approveDate = $request->approve_date ?? session('por3_approve_date');

        $section = ClassSection::with(['level', 'semester.academicYear'])->findOrFail($sectionId);

        $studentSections = StudentSection::with(['student.families'])
            ->where('section_id', $sectionId)
            ->orderBy('student_number')
            ->get();

        $studentIds = $studentSections->pluck('student_id');
        $semesterId = $section->semester_id;

        $docNumbers = StudentDocNumber::whereIn('student_id', $studentIds)
            ->where('semester_id', $semesterId)
            ->get()->keyBy('student_id');

        $pp2Docs = Pp2Document::whereIn('student_id', $studentIds)
            ->where('section_id', $sectionId)
            ->get()->keyBy('student_id');

        // คำนวณหน่วยกิตตลอดหลักสูตรจากทุกเทอมที่เรียน
        $allGrades = FinalGrade::with(['teachingAssign.subject'])
            ->whereIn('student_id', $studentIds)
            ->get()->groupBy('student_id');

        $creditsByStudent = [];
        $gpaTotalByStudent = [];
        foreach ($allGrades as $sid => $grades) {
            $credits = 0;
            $gpaSum = 0;
            $gpaCount = 0;
            foreach ($grades as $g) {
                $subj = $g->teachingAssign->subject ?? null;
                if (!$subj) continue;
                $cr = (float)($subj->credits ?? 0);
                if (($subj->subject_group ?? '') !== 'กิจกรรมพัฒนาผู้เรียน') {
                    $credits += $cr;
                    if ($cr > 0 && $g->gpa_point !== null) {
                        $gpaSum += (float)$g->gpa_point;
                        $gpaCount++;
                    }
                }
            }
            $creditsByStudent[$sid] = $credits;
            $gpaTotalByStudent[$sid] = $gpaCount > 0 ? round($gpaSum / $gpaCount, 2) : 0;
        }

        $approver = $approverId ? Personnel::find($approverId) : null;
        $school   = config('school');

        $approveDateFormatted = '';
        if ($approveDate) {
            $months = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
            $d = \Carbon\Carbon::parse($approveDate);
            $approveDateFormatted = $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
        }

        $maleCount   = $studentSections->filter(fn($ss) => ($ss->student?->gender ?? '') === 'M')->count();
        $femaleCount = $studentSections->filter(fn($ss) => ($ss->student?->gender ?? '') === 'F')->count();

        return view('academic.por3_print', compact(
            'section', 'studentSections', 'docNumbers', 'pp2Docs',
            'creditsByStudent', 'gpaTotalByStudent',
            'approver', 'school', 'approveDateFormatted',
            'maleCount', 'femaleCount'
        ));
    }
}
