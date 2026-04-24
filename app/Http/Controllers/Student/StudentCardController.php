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

        if ($request->filled('section_id')) {
            // กรองตามห้องเรียน → ใช้ StudentSection
            $students = StudentSection::with([
                'student',
                'classSection.level',
                'classSection.semester.academicYear',
            ])->where('status', 'กำลังศึกษา')
              ->where('section_id', $request->section_id)
              ->orderBy('student_number')
              ->get()
              ->map(fn($ss) => (object)['student' => $ss->student]);
        } elseif ($request->filled('search')) {
            // ค้นหาด้วยชื่อ/รหัส → ค้นจาก Student โดยตรง
            $s = $request->search;
            $students = Student::where(fn($q) =>
                    $q->where('student_code', 'like', "%$s%")
                      ->orWhere('thai_firstname', 'like', "%$s%")
                      ->orWhere('thai_lastname', 'like', "%$s%")
                )
                ->orderBy('thai_firstname')
                ->get()
                ->map(fn($student) => (object)['student' => $student]);
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

    // พิมพ์ทั้งห้อง / พิมพ์ผลการค้นหาทั้งหมด
    public function printAll(Request $request)
    {
        if ($request->filled('section_id')) {
            $rows = StudentSection::with([
                'student',
                'classSection.level',
                'classSection.semester.academicYear',
            ])->where('status', 'กำลังศึกษา')
              ->where('section_id', $request->section_id)
              ->orderBy('student_number')->get();

            $students = $rows->map(fn($ss) => ['student' => $ss->student, 'ss' => $ss]);
        } elseif ($request->filled('search')) {
            $s = $request->search;
            $students = Student::where(fn($q) =>
                    $q->where('student_code', 'like', "%$s%")
                      ->orWhere('thai_firstname', 'like', "%$s%")
                      ->orWhere('thai_lastname', 'like', "%$s%")
                )
                ->orderBy('thai_firstname')
                ->get()
                ->map(fn($student) => ['student' => $student, 'ss' => null]);
        } else {
            $students = collect();
        }

        return view('student.student_card_print', compact('students'));
    }
}
