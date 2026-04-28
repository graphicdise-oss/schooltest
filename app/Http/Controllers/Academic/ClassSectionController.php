<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassSectionController extends Controller
{
    public function index(Request $request)
    {
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();
        $levels = Level::orderBy('sort_order')->get();
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');

        $sections = ClassSection::with(['level', 'homeroomTeacher', 'studentSections'])
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        return view('academic.class_sections', compact('sections', 'semesters', 'levels', 'semesterId'));
    }

    public function store(Request $request)
    {
        $request->validate(['semester_id' => 'required', 'level_id' => 'required', 'section_number' => 'required|integer']);
        ClassSection::create($request->only(['semester_id', 'level_id', 'section_number', 'homeroom_teacher_id', 'max_students']));
        return redirect()->back()->with('success', 'เพิ่มห้องเรียนสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        ClassSection::findOrFail($id)->update($request->only(['section_number', 'homeroom_teacher_id', 'max_students']));
        return redirect()->back()->with('success', 'แก้ไขสำเร็จ');
    }

    public function destroy($id)
    {
        ClassSection::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบห้องเรียนสำเร็จ');
    }

    // หน้าจัดนักเรียนเข้าห้อง
    public function manageStudents($id)
    {
        $section = ClassSection::with(['level', 'semester.academicYear', 'studentSections.student'])->findOrFail($id);

        // นักเรียนที่ยังไม่มีห้องในเทอมนี้
        $assignedIds = StudentSection::where('section_id', $id)->pluck('student_id');
        $availableStudents = Student::where('status', 'กำลังศึกษา')
            ->whereNotIn('student_id', $assignedIds)
            ->orderBy('thai_firstname')
            ->get();

        return view('academic.section_students', compact('section', 'availableStudents'));
    }

    public function assignStudents(Request $request, $id)
    {
        $request->validate(['student_ids' => 'required|array']);
        $section = ClassSection::findOrFail($id);

        $lastNumber = StudentSection::where('section_id', $id)->max('student_number') ?? 0;

        foreach ($request->student_ids as $studentId) {
            $lastNumber++;
            StudentSection::firstOrCreate(
                ['student_id' => $studentId, 'section_id' => $id],
                ['student_number' => $lastNumber, 'status' => 'กำลังศึกษา']
            );
        }

        return redirect()->back()->with('success', 'จัดนักเรียนเข้าห้องสำเร็จ');
    }

    public function removeStudent($id, $ssId)
    {
        StudentSection::where('id', $ssId)->where('section_id', $id)->delete();
        return redirect()->back()->with('success', 'นำนักเรียนออกจากห้องสำเร็จ');
    }

    public function renumberStudents($id)
    {
        $students = StudentSection::where('section_id', $id)
            ->join('students', 'student_sections.student_id', '=', 'students.student_id')
            ->orderByRaw('students.enroll_date ASC NULLS LAST')
            ->orderBy('students.created_at')
            ->select('student_sections.*')
            ->get();

        foreach ($students as $i => $ss) {
            $ss->update(['student_number' => $i + 1]);
        }

        return redirect()->back()->with('success', 'เรียงเลขที่นักเรียนใหม่สำเร็จ');
    }
}