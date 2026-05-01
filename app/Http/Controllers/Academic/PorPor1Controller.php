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
use App\Models\Student;
use Illuminate\Http\Request;

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

        if ($sectionId) {
            $currentSection = ClassSection::with('level')->find($sectionId);
            $rows = StudentSection::with(['student'])
                ->where('section_id', $sectionId)
                ->where('status', 'กำลังศึกษา')
                ->orderBy('student_number')
                ->get();
            $students = $rows->map(fn($ss) => $ss->student)->filter();
        } elseif ($search) {
            $students = Student::where('status', 'กำลังศึกษา')
                ->where(fn($q) => $q
                    ->where('student_code', 'like', "%$search%")
                    ->orWhere('thai_firstname', 'like', "%$search%")
                    ->orWhere('thai_lastname', 'like', "%$search%")
                )->orderBy('thai_firstname')->get();
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

        return view('academic.por1_index', compact(
            'academicYears', 'levels', 'sections', 'students', 'docNumbers',
            'yearId', 'term', 'levelId', 'semesterId', 'sectionId', 'search',
            'currentSection', 'studentSemesters'
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

            if (!isset($yearGroups[$yearName])) {
                $yearGroups[$yearName] = ['year' => $yearName, 'level' => $levelName, 'semesters' => []];
            }
            if (!isset($yearGroups[$yearName]['semesters'][$semName])) {
                $yearGroups[$yearName]['semesters'][$semName] = [];
            }
            $yearGroups[$yearName]['semesters'][$semName][] = $grade;
        }

        ksort($yearGroups);

        $father = $student->families->firstWhere('guardian_type', 'บิดา')
            ?? $student->families->firstWhere('family_type', 'บิดา');
        $mother = $student->families->firstWhere('guardian_type', 'มารดา')
            ?? $student->families->firstWhere('family_type', 'มารดา');

        return view('academic.por1_print', compact('student', 'docNumber', 'yearGroups', 'father', 'mother'));
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
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
            'doc_set'     => 'required|string|max:20',
        ]);

        $section = ClassSection::findOrFail($request->section_id);

        $rows = StudentSection::where('section_id', $request->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        foreach ($rows as $i => $ss) {
            StudentDocNumber::updateOrCreate(
                ['student_id' => $ss->student_id, 'semester_id' => $request->semester_id],
                [
                    'level_id'   => $section->level_id,
                    'doc_set'    => $request->doc_set,
                    'doc_number' => str_pad($i + 1, 5, '0', STR_PAD_LEFT),
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
