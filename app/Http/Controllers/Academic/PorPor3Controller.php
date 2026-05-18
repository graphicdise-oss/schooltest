<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;

class PorPor3Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();
        $currentSem    = Semester::where('is_current', true)->with('academicYear')->first();

        $yearId    = $request->year_id   ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $levelId   = $request->level_id;
        $sectionId = $request->section_id;
        $search    = trim($request->search ?? '');

        // ดึง semester_ids ทั้งหมดของปีการศึกษาที่เลือก
        $semesterIds = Semester::where('year_id', $yearId)->pluck('semester_id');

        $levels = Level::whereHas('classSections', fn($q) => $q->whereIn('semester_id', $semesterIds))
            ->orderBy('sort_order')->get();

        $sections = ClassSection::with('level')
            ->whereIn('semester_id', $semesterIds)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        $currentSection = null;
        if ($sectionId && $sectionId !== 'all') {
            $currentSection = ClassSection::with('level')->find($sectionId);
        }

        // ค้นหานักเรียนที่จบการศึกษาในปีการศึกษาที่เลือก (ไม่กรองตามเทอม เพราะจบได้ทุกเทอม)
        $query = StudentSection::with(['student', 'classSection.level'])
            ->where('status', 'จบการศึกษา')
            ->whereHas('classSection', fn($q) => $q->whereIn('semester_id', $semesterIds));

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

        return view('academic.por3_index', compact(
            'academicYears', 'levels', 'sections', 'students',
            'yearId', 'levelId', 'sectionId', 'search',
            'currentSection'
        ));
    }
}
