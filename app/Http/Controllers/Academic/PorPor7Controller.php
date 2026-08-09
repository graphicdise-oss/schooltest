<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Pp2Setting;
use App\Models\Student;
use Illuminate\Http\Request;

class PorPor7Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();

        $currentSem = Semester::where('is_current', true)->with('academicYear')->first();
        $yearId    = $request->year_id   ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $term      = $request->term      ?? ($currentSem->semester_name ?? '1');
        $levelId   = $request->level_id;
        $sectionId = $request->section_id;
        $search    = trim($request->search ?? '');

        $semester   = Semester::where('year_id', $yearId)->where('semester_name', $term)->first();
        $semesterId = $semester?->semester_id;

        $levels = Level::whereHas('classSections', fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('sort_order')
            ->get();

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
            ->whereHas('classSection', fn($q) => $q->where('semester_id', $semesterId))
            ->where('status', 'กำลังศึกษา');

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

        return view('academic.por7_index', compact(
            'academicYears', 'levels', 'sections', 'students',
            'yearId', 'term', 'levelId', 'sectionId', 'search',
            'semesterId', 'currentSection'
        ));
    }

    public function print(Request $request, $studentId)
    {
        $student = Student::with(['families', 'education'])->findOrFail($studentId);

        $semesterId = $request->semester_id;

        // ข้อมูลห้องเรียนปัจจุบัน
        $studentSection = StudentSection::with(['classSection.level', 'classSection.semester.academicYear'])
            ->where('student_id', $studentId)
            ->where('status', 'กำลังศึกษา')
            ->when($semesterId, fn($q) => $q->whereHas('classSection', fn($q2) => $q2->where('semester_id', $semesterId)))
            ->latest()
            ->first();

        if (!$studentSection) {
            $studentSection = StudentSection::with(['classSection.level', 'classSection.semester.academicYear'])
                ->where('student_id', $studentId)
                ->latest()
                ->first();
        }

        $section  = $studentSection?->classSection;
        $level    = $section?->level;
        $semester = $section?->semester;
        $yearName = $semester?->academicYear?->year_name ?? '';

        $levelSection = ($level?->name ?? '') . '/' . ($section?->section_number ?? '');

        $father = $student->families->firstWhere('guardian_type', 'บิดา')
            ?? $student->families->firstWhere('family_type', 'บิดา');
        $mother = $student->families->firstWhere('guardian_type', 'มารดา')
            ?? $student->families->firstWhere('family_type', 'มารดา');

        $school = Pp2Setting::mergedSchoolConfig();

        $issueDate   = $request->issue_date ?? date('Y-m-d');
        $gradeResult = $request->grade_result ?? '';
        $behavior    = $request->behavior ?? '';

        $issueDateFormatted = $this->formatThaiDate($issueDate);

        $dob = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth) : null;
        $thMonths = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                     'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $dobFormatted = $dob
            ? $dob->day . ' ' . $thMonths[$dob->month] . ' ' . ($dob->year + 543)
            : '';

        return view('academic.por7_print', compact(
            'student', 'school',
            'level', 'section', 'levelSection', 'yearName',
            'father', 'mother',
            'issueDateFormatted', 'gradeResult', 'behavior',
            'dobFormatted'
        ));
    }

    // ตั้งค่าข้อมูลโรงเรียน/ผู้ลงนาม/ตราโรงเรียน ย้ายไปหน้ากลาง "ตั้งค่าเริ่มต้น" แล้ว
    // (App\Http\Controllers\Setting\SchoolSettingController) — ยังใช้ Pp2Setting ตัวเดียวกัน แค่จุดแก้ไขรวมที่เดียว

    private function formatThaiDate(string $dateStr): string
    {
        $months = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                   'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $d = \Carbon\Carbon::parse($dateStr);
        return $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
    }
}
