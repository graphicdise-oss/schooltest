<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use Illuminate\Http\Request;

class StudentListController extends Controller
{
    /**
     * แสดงรายการนักเรียน + ค้นหา/กรอง
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('level_id') || $request->filled('section_id') || $request->filled('academic_year') || $request->filled('semester')) {
            $query->whereHas('studentSections.classSection', function ($q) use ($request) {
                if ($request->filled('level_id')) {
                    $q->where('level_id', $request->level_id);
                }
                if ($request->filled('section_id')) {
                    $q->where('section_id', $request->section_id);
                }
                if ($request->filled('academic_year') || $request->filled('semester')) {
                    $q->whereHas('semester', function ($sq) use ($request) {
                        if ($request->filled('semester')) {
                            $sq->where('semester_name', $request->semester);
                        }
                        if ($request->filled('academic_year')) {
                            $sq->whereHas('academicYear', fn($aq) => $aq->where('year_name', $request->academic_year));
                        }
                    });
                }
            });
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

        if ($request->filled('search_idcard')) {
            $query->where('id_card_number', 'like', "%{$request->search_idcard}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

       

        $students = $query->orderBy('classroom_number', 'asc')->paginate(20);

        $levels = Level::orderBy('sort_order')->get();

        $classrooms = ClassSection::with('level')
            ->orderBy('level_id')->orderBy('section_number')
            ->get()
            ->map(fn($s) => [
                'section_id' => $s->section_id,
                'level_id' => $s->level_id,
                'label' => ($s->level->name ?? '?') . '/' . $s->section_number,
            ]);

        return view('student.student_index', compact('students', 'levels', 'classrooms'));
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