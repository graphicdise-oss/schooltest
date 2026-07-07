<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;

class ClassRosterController extends Controller
{
    public function index(Request $request)
    {
        $yearId = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId = $request->get('level_id');
        $sectionId = $request->get('section_id');
        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $currentYear = AcademicYear::current();

        // default ปีปัจจุบัน
        if (!$yearId && $currentYear)
            $yearId = $currentYear->year_id;

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        // แทนด้วย
        if (!$semesterId && $yearId) {
            $defaultSem = Semester::where('year_id', $yearId)
                ->where('is_current', true)
                ->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();

            if ($defaultSem)
                $semesterId = $defaultSem->semester_id;
        }

        $levels = Level::orderBy('sort_order')->get();

        // sections ของ level+semester ที่เลือก
        $sections = collect();
        if ($levelId && $semesterId) {
            $sections = ClassSection::with('level')
                ->where('level_id', $levelId)
                ->where('semester_id', $semesterId)
                ->orderBy('section_number')
                ->get();
        }

        // default section แรก
        if (!$sectionId && $sections->isNotEmpty()) {
            $sectionId = $sections->first()->section_id;
        }

        // นักเรียนในห้องที่เลือก
        $students = collect();
        if ($sectionId) {
            $query = StudentSection::with('student')
                ->where('section_id', $sectionId)
                ->orderBy('student_number');

            if ($search) {
                $query->whereHas(
                    'student',
                    fn($q) => $q
                        ->where('thai_firstname', 'like', "%{$search}%")
                        ->orWhere('thai_lastname', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%")
                );
            }
            if ($status) {
                $query->whereHas('student', fn($q) => $q->where('status', $status));
            }

            $students = $query->get();
        }

        $selectedSection = $sectionId
            ? ClassSection::with('level')->find($sectionId)
            : null;

        return view('student.class_roster', compact(
            'academicYears',
            'semesters',
            'levels',
            'sections',
            'students',
            'selectedSection',
            'yearId',
            'semesterId',
            'levelId',
            'sectionId',
            'search',
            'status'
        ));
    }
}