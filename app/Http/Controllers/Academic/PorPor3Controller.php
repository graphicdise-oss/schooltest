<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Student;
use Illuminate\Http\Request;

class PorPor3Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();
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

        $sections = $semesterId
            ? ClassSection::with('level')
                ->where('semester_id', $semesterId)
                ->when($levelId, fn($q) => $q->where('level_id', $levelId))
                ->orderBy('level_id')->orderBy('section_number')
                ->get()
            : collect();

        $query = StudentSection::with(['student', 'classSection.level', 'classSection.semester.academicYear'])
            ->where('status', 'จบการศึกษา')
            ->whereHas('classSection.semester', fn($q) => $q->where('year_id', $yearId)->where('semester_name', $term));

        if ($levelId) {
            $query->whereHas('classSection', fn($q) => $q->where('level_id', $levelId));
        }
        if ($sectionId && $sectionId !== 'all') {
            $query->where('section_id', $sectionId);
        }
        if ($search !== '') {
            $query->whereHas('student', fn($q) => $q
                ->where('thai_firstname', 'like', "%{$search}%")
                ->orWhere('thai_lastname',  'like', "%{$search}%")
                ->orWhere('student_code',   'like', "%{$search}%")
            );
        }

        $rows = $query->orderBy('student_number')->get();

        $students = $rows->map(fn($ss) => [
            'student' => $ss->student,
            'section' => $ss->classSection,
        ])->filter(fn($r) => $r['student']);

        return view('academic.por3_index', compact(
            'academicYears', 'levels', 'sections', 'students',
            'yearId', 'term', 'levelId', 'sectionId', 'search', 'semesterId'
        ));
    }
}
