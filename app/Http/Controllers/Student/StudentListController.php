<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentListController extends Controller
{
    /**
     * แสดงรายการนักเรียน + ค้นหา/กรอง
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('classroom')) {
            $query->where('classroom', $request->classroom);
        }

        if ($request->filled('search_name')) {
            $name = $request->search_name;
            $query->where(function ($q) use ($name) {
                $q->where('thai_firstname', 'like', "%{$name}%")
                    ->orWhere('thai_lastname', 'like', "%{$name}%");
            });
        }

        if ($request->filled('search_code')) {
            $query->where('student_code', 'like', "%{$request->search_code}%");
        }

        if ($request->filled('search_id_card')) {
            $query->where('id_card_number', 'like', "%{$request->search_id_card}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('enroll_status')) {
            $query->where('enroll_status', $request->enroll_status);
        }

        $students = $query->orderBy('classroom_number', 'asc')
            ->paginate(20);

        return view('student.student_index', compact('students'));

    }

    /**
     * ดูรายละเอียดนักเรียน
     */
    public function show($id)
    {
        $student = Student::with(['addresses', 'education'])
            ->where('student_id', $id)
            ->firstOrFail();

        return view('student.student_index', compact('students'));

    }

    /**
     * ลบข้อมูลนักเรียน
     */
    public function destroy($id)
    {
        $student = Student::where('student_id', $id)->firstOrFail();
        $student->delete();

        return redirect()->route('student.student_index')
            ->with('success', 'ลบข้อมูลนักเรียนสำเร็จ');
    }
}