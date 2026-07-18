<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Promotion;
use App\Models\Academic\Pp2Setting;
use App\Models\Personne\Personnel;
use App\Models\Pp2SectionSetting;
use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PorPor1Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();

        // ค่า default: ปีและเทอมปัจจุบัน
        $currentSem = Semester::where('is_current', true)->with('academicYear')->first();
        $yearId   = $request->year_id   ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $term     = $request->term      ?? ($currentSem->semester_name ?? '1');
        $levelId  = $request->level_id;
        $sectionId = $request->section_id;
        $search   = $request->search;

        // หา semester จาก year + term
        $semester = Semester::where('year_id', $yearId)
            ->where('semester_name', $term)
            ->first();
        $semesterId = $semester?->semester_id;

        // ระดับชั้นที่มีในเทอมนี้
        $levels = Level::whereHas('classSections', fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('sort_order')
            ->get();

        // ห้องเรียน (กรองตาม level ถ้าเลือก)
        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        $students = collect();
        $currentSection = null;
        $rows = collect();

      if ($sectionId === 'all') {
        $sectionIds = $sections->pluck('section_id');
        $rows = StudentSection::with(['student'])
            ->whereIn('section_id', $sectionIds)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();
        $students = $rows->map(fn($ss) => $ss->student)->filter()->unique('student_id')->values();
    } elseif ($sectionId) {
        $currentSection = ClassSection::with('level')->find($sectionId);
        $rows = StudentSection::with(['student'])
            ->where('section_id', $sectionId)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();
        $students = $rows->map(fn($ss) => $ss->student)->filter();
        }

        // หา issued_date จาก Pp2SectionSetting สำหรับแต่ละนักเรียน
        $studentApproveDates = [];
        if ($rows->count()) {
            $secIds = $rows->pluck('section_id')->unique();
            $sectionDates = Pp2SectionSetting::whereIn('section_id', $secIds)
                ->pluck('issued_date', 'section_id');
            foreach ($rows as $ss) {
                $d = $sectionDates[$ss->section_id] ?? null;
                $studentApproveDates[$ss->student_id] = $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : '';
            }
        }

        $docNumbers = [];
        if ($students->count() && $semesterId) {
            $ids = $students->pluck('student_id');
            StudentDocNumber::whereIn('student_id', $ids)
                ->where('semester_id', $semesterId)
                ->get()
                ->each(fn($d) => $docNumbers[$d->student_id] = $d);
        }

        $studentSemesters = [];
        foreach ($students as $stu) {
            $studentSemesters[$stu->student_id] = $this->buildStudentSemesters($stu->student_id);
        }

        $personnels = Personnel::where('status', 'ปฏิบัติงาน')
            ->orderBy('thai_firstname')
            ->get(['personnel_id', 'thai_prefix', 'thai_firstname', 'thai_lastname', 'position']);
        $signSettings = Pp2Setting::getInstance();

        // หาช่วงเลขที่ถูกใช้ไปแล้วในเทอมนี้ (สำหรับแสดงคำแนะนำ)
        $docNumRange = null;
        if ($semesterId) {
            $nums = StudentDocNumber::where('semester_id', $semesterId)
                ->whereNotNull('doc_number')
                ->get()
                ->map(fn($d) => (int)$d->doc_number)
                ->filter()
                ->sort()
                ->values();
            if ($nums->count()) {
                $docNumRange = ['min' => $nums->first(), 'max' => $nums->last(), 'count' => $nums->count()];
            }
        }

        return view('academic.por1_index', compact(
            'academicYears', 'levels', 'sections', 'students', 'docNumbers',
            'yearId', 'term', 'levelId', 'semesterId', 'sectionId', 'search',
            'currentSection', 'studentSemesters', 'studentApproveDates',
            'personnels', 'signSettings', 'docNumRange'
        ));
    }

    public function printOne(Request $request, $studentId)
    {
        $student = Student::with(['education', 'families', 'addresses'])->findOrFail($studentId);

        $semesterId = $request->semester_id;
        $docNumber  = StudentDocNumber::where('student_id', $studentId)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->latest()->first();

        $selectedSemesters = $request->semesters ?? [];
        $filterActive = $request->boolean('filter_active');

        $grades = FinalGrade::with([
            'teachingAssign.subject',
            'teachingAssign.classSection.level',
            'semester.academicYear',
        ])->where('student_id', $studentId)->get();

        $yearGroups = [];
        foreach ($grades as $grade) {
            $yearName  = $grade->semester->academicYear->year_name ?? 'ไม่ระบุ';
            $semName   = $grade->semester->semester_name ?? '1';
            $levelName = $grade->teachingAssign->classSection->level->name ?? '';

            if ($filterActive && !in_array($yearName . '/' . $semName, $selectedSemesters)) {
                continue;
            }

            $groupKey = $yearName . '|' . $levelName;
            if (!isset($yearGroups[$groupKey])) {
                $yearGroups[$groupKey] = ['year' => $yearName, 'level' => $levelName, 'semesters' => []];
            }
            if (!isset($yearGroups[$groupKey]['semesters'][$semName])) {
                $yearGroups[$groupKey]['semesters'][$semName] = [];
            }
            $yearGroups[$groupKey]['semesters'][$semName][] = $grade;
        }

        ksort($yearGroups);

        $father = $student->families->firstWhere('guardian_type', 'บิดา')
            ?? $student->families->firstWhere('family_type', 'บิดา');
        $mother = $student->families->firstWhere('guardian_type', 'มารดา')
            ?? $student->families->firstWhere('family_type', 'มารดา');

        // วันอนุมัติการจบ: รับจาก request (ตั้งค่าใน modal) หรือดึงจาก Pp2SectionSetting
        $approveDate = '';
        if ($request->filled('approve_date')) {
            $approveDate = $this->formatThaiDate($request->input('approve_date'));
        } else {
            $studentSectionIds = StudentSection::where('student_id', $studentId)->pluck('section_id');
            $setting = Pp2SectionSetting::whereIn('section_id', $studentSectionIds)->first();
            $approveDate = $setting?->issued_date
                ? $this->formatThaiDate($setting->issued_date->format('Y-m-d'))
                : '';
        }

        // วันออกจากโรงเรียน และ สาเหตุ จาก Promotion
        $promotion = Promotion::where('student_id', $studentId)->latest('promo_date')->first();
        $leaveDate   = $promotion?->promo_date ? $this->formatThaiDate($promotion->promo_date->format('Y-m-d')) : '';
        $leaveReason = $promotion?->remark ?? '';

        // ข้อมูลลายเซ็น: ดึงจาก Pp2Setting (DB) ก่อน ถ้าไม่มีใช้ config
        $signSettings = Pp2Setting::getInstance();
        $school = config('school');
        if ($signSettings->registrar_name) {
            $school['registrar_name'] = $signSettings->registrar_name;
        }
        if ($signSettings->director_name) {
            $school['director_name'] = $signSettings->director_name;
        }

        return Pdf::loadView('academic.por1_print', compact(
            'student', 'docNumber', 'yearGroups', 'father', 'mother',
            'approveDate', 'leaveDate', 'leaveReason', 'school'
        ))->download("por1_{$student->student_code}.pdf");
    }

    public function saveSignSettings(Request $request)
    {
        $request->validate([
            'registrar_personnel_id' => 'nullable|integer',
            'director_personnel_id'  => 'nullable|integer',
        ]);

        $setting = Pp2Setting::getInstance();
        if (!$setting->exists) {
            $setting->save();
        }

        $registrar = $request->registrar_personnel_id
            ? Personnel::find($request->registrar_personnel_id)
            : null;
        $director  = $request->director_personnel_id
            ? Personnel::find($request->director_personnel_id)
            : null;

        $setting->update([
            'registrar_personnel_id' => $registrar?->personnel_id,
            'director_personnel_id'  => $director?->personnel_id,
            'registrar_name' => $registrar
                ? trim(($registrar->thai_prefix ?? '') . $registrar->thai_firstname . ' ' . $registrar->thai_lastname)
                : $request->registrar_name_manual,
            'director_name'  => $director
                ? trim(($director->thai_prefix ?? '') . $director->thai_firstname . ' ' . $director->thai_lastname)
                : $request->director_name_manual,
        ]);

        return redirect()->back()->with('success', 'บันทึกชื่อผู้ลงนามสำเร็จ');
    }

    private function formatThaiDate(string $dateStr): string
    {
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                   'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $d = \Carbon\Carbon::parse($dateStr);
        return $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
    }

    public function setDocNumber(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|integer',
            'semester_id' => 'required|integer',
            'level_id'    => 'required|integer',
            'doc_set'     => 'nullable|string|max:20',
            'doc_number'  => 'nullable|string|max:20',
        ]);

        StudentDocNumber::updateOrCreate(
            ['student_id' => $request->student_id, 'semester_id' => $request->semester_id],
            [
                'level_id'   => $request->level_id,
                'doc_set'    => $request->doc_set,
                'doc_number' => $request->doc_number,
            ]
        );

        return redirect()->back()->with('success', 'บันทึกเลขที่เอกสารสำเร็จ');
    }

    public function bulkSetDocSet(Request $request)
    {
        $request->validate([
            'section_id'   => 'required|integer',
            'semester_id'  => 'required|integer',
            'doc_set'      => 'required|string|max:20',
            'start_number' => 'nullable|integer|min:1',
        ]);

        $section = ClassSection::findOrFail($request->section_id);

        $rows = StudentSection::with('student')
            ->where('section_id', $request->section_id)
            ->where('status', 'กำลังศึกษา')
            ->get()
            ->sortBy(fn($ss) => $ss->student?->student_code)
            ->values();

        $startNum = (int)($request->start_number ?? 1);
        $count    = $rows->count();

        // ตรวจสอบเลขซ้ำ: หาเลขที่ใช้ในเทอมนี้จาก section อื่น
        $newNumbers = range($startNum, $startNum + $count - 1);
        $ownStudentIds = $rows->pluck('student_id');

        $usedNums = StudentDocNumber::where('semester_id', $request->semester_id)
            ->whereNotIn('student_id', $ownStudentIds)
            ->whereNotNull('doc_number')
            ->get()
            ->map(fn($d) => (int)$d->doc_number)
            ->filter()
            ->toArray();

        $conflicts = array_intersect($newNumbers, $usedNums);
        if (!empty($conflicts)) {
            $usedSorted = collect($usedNums)->sort()->values();
            $min = $usedSorted->first();
            $max = $usedSorted->last();
            $suggested = $max + 1;
            return redirect()->back()->with(
                'warning',
                "เลขที่ซ้ำกับที่ใช้ไปแล้ว (ช่วงที่ใช้แล้ว: {$min} – {$max}) กรุณาเริ่มจาก {$suggested} หรือมากกว่า"
            );
        }

        foreach ($rows as $i => $ss) {
            StudentDocNumber::updateOrCreate(
                ['student_id' => $ss->student_id, 'semester_id' => $request->semester_id],
                [
                    'level_id'   => $section->level_id,
                    'doc_set'    => $request->doc_set,
                    'doc_number' => str_pad($startNum + $i, 5, '0', STR_PAD_LEFT),
                ]
            );
        }

        return redirect()->back()->with('success', 'ตั้งเลขชุดทั้งห้องสำเร็จ (' . $rows->count() . ' คน)');
    }

    private function buildStudentSemesters($studentId): array
    {
        $sections = StudentSection::with([
                'classSection.level',
                'classSection.semester.academicYear',
            ])
            ->where('student_id', $studentId)
            ->get();

        $byLevel = [];
        $currentOrder = 0;

        foreach ($sections as $ss) {
            $level    = $ss->classSection->level ?? null;
            $semester = $ss->classSection->semester ?? null;
            $year     = $semester?->academicYear ?? null;
            if (!$level || !$semester || !$year) continue;

            $levelName  = $level->name;
            $levelOrder = $level->sort_order ?? 0;
            $semKey     = $year->year_name . '/' . $semester->semester_name;

            if (!isset($byLevel[$levelName])) {
                $byLevel[$levelName] = ['sort_order' => $levelOrder, 'semesters' => []];
            }
            $byLevel[$levelName]['semesters'][$semKey] = [
                'key'  => $semKey,
                'year' => $year->year_name,
                'term' => $semester->semester_name,
            ];

            if ($ss->status === 'กำลังศึกษา' && $levelOrder > $currentOrder) {
                $currentOrder = $levelOrder;
            }
        }

        if ($currentOrder === 0 && !empty($byLevel)) {
            $currentOrder = max(array_column($byLevel, 'sort_order'));
        }

        uasort($byLevel, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
        foreach ($byLevel as &$lvl) {
            ksort($lvl['semesters']);
        }

        return ['levels' => $byLevel, 'currentLevelOrder' => $currentOrder];
    }
}
