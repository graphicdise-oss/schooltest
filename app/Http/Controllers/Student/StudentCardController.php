<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentCardController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_id', 'desc')->get();
        $levels        = Level::orderBy('sort_order')->get();

        $semesters = $request->filled('year_id')
            ? Semester::where('year_id', $request->year_id)->orderBy('semester_id')->get()
            : collect();

        $sections = ($request->filled('semester_id') && $request->filled('level_id'))
            ? ClassSection::with('level')
                ->where('semester_id', $request->semester_id)
                ->where('level_id', $request->level_id)
                ->orderBy('section_number')->get()
            : collect();

        $students = collect();

        if ($request->filled('section_id') || $request->filled('search')) {
            $query = StudentSection::with([
                'student',
                'classSection.level',
                'classSection.semester.academicYear',
            ])->where('status', 'กำลังศึกษา');

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            } elseif ($request->filled('semester_id')) {
                $query->whereHas('classSection', fn($q) =>
                    $q->where('semester_id', $request->semester_id)
                );
            }

            if ($request->filled('search')) {
                $s = $request->search;
                $query->whereHas('student', fn($q) =>
                    $q->where('student_code', 'like', "%$s%")
                      ->orWhere('thai_firstname', 'like', "%$s%")
                      ->orWhere('thai_lastname', 'like', "%$s%")
                );
            }

            $students = $query->orderBy('student_number')->get();
        }

        return view('student.student_card_index', compact(
            'academicYears', 'levels', 'semesters', 'sections', 'students'
        ));
    }

    // พิมพ์บัตรคนเดียว
    public function printOne($id)
    {
        $student = Student::findOrFail($id);
        $ss = StudentSection::with(['classSection.level', 'classSection.semester.academicYear'])
            ->where('student_id', $id)
            ->where('status', 'กำลังศึกษา')
            ->latest()->first();

        $students = collect([['student' => $student, 'ss' => $ss]]);
        return view('student.student_card_print', compact('students'));
    }

    // พิมพ์ทั้งห้อง
    public function printAll(Request $request)
    {
        $query = StudentSection::with([
            'student',
            'classSection.level',
            'classSection.semester.academicYear',
        ])->where('status', 'กำลังศึกษา');

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $rows = $query->orderBy('student_number')->get();

        $students = $rows->map(fn($ss) => ['student' => $ss->student, 'ss' => $ss]);
        return view('student.student_card_print', compact('students'));
    }
}
