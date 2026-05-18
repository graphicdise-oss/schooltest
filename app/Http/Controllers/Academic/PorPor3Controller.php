<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;

class PorPor3Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        $yearId    = $request->get('year_id');
        $levelId   = $request->get('level_id');
        $sectionId = $request->get('section_id');
        $search    = $request->get('search', '');

        if (!$request->has('year_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
            $yearId = $currentYear?->year_id;
        }

        $levels = Level::orderBy('sort_order')->get();

        $sections = ($levelId && $yearId)
            ? ClassSection::with('level')
                ->where('level_id', $levelId)
                ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
                ->orderBy('section_number')
                ->get()
            : collect();

        $students = collect();

        if ($sectionId) {
            $query = StudentSection::with(['student', 'classSection.level'])
                ->where('section_id', $sectionId)
                ->where('status', 'จบการศึกษา');

            if ($search) {
                $query->whereHas('student', fn($q) => $q
                    ->where('thai_firstname', 'like', "%$search%")
                    ->orWhere('thai_lastname', 'like', "%$search%")
                    ->orWhere('student_code', 'like', "%$search%")
                );
            }

            $students = $query->orderBy('student_number')->get();
        }

        return view('academic.por3_index', compact(
            'academicYears', 'levels', 'sections', 'students',
            'yearId', 'levelId', 'sectionId', 'search'
        ));
    }
}
