<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\AcademicYear;
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

    /**
     * รายงานชื่อนักเรียนใหม่ + วันที่เข้าเรียนล่าสุด
     *
     * ตาราง students ไม่มีคอลัมน์บอกสถานะ "เข้าใหม่/ย้ายมา" (ตัวเลือกในฟอร์มค้นหา
     * เป็นแค่ dropdown ที่ไม่เคยผูกกับคอลัมน์จริง) จึงนิยาม "นักเรียนใหม่" จากข้อมูล
     * ที่มีจริง: นักเรียนที่ถูกจัดเข้าห้องเรียนครั้งแรกสุดในปีการศึกษาปัจจุบัน
     */
    public function newStudentsReport(Request $request)
    {
        $levels  = Level::orderBy('sort_order')->get();
        $levelId = $request->get('level_id', '');
        $search  = $request->get('search', '');

        $currentYearId = optional(AcademicYear::current())->year_id;

        $students = Student::whereHas('studentSections')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('thai_firstname', 'like', "%$search%")
                       ->orWhere('thai_lastname', 'like', "%$search%")
                       ->orWhere('student_code', 'like', "%$search%");
                });
            })
            ->with(['studentSections' => function ($q) {
                $q->with(['classSection.level', 'classSection.semester.academicYear'])
                  ->orderBy('created_at'); // เรียงเก่า -> ใหม่ เพื่อหาห้องแรกสุดได้ง่าย
            }])
            ->get();

        // แปลงเป็นแถวรายงาน (ห้องแรกสุด = วันเข้าเรียนครั้งแรก, ห้องล่าสุด = วันเข้าเรียนล่าสุด)
        $rows = $students->map(function ($s) {
            $first  = $s->studentSections->first();
            $latest = $s->studentSections->last();
            $sec    = $latest?->classSection;
            return (object) [
                'code'          => $s->student_code,
                'name'          => trim(($s->thai_prefix ?? '') . ($s->thai_firstname ?? '') . ' ' . ($s->thai_lastname ?? '')),
                'gender'        => $s->gender,
                'room'          => $sec ? (($sec->level->name ?? '') . '/' . $sec->section_number) : '-',
                'year'          => $sec?->semester?->academicYear?->year_name,
                'enroll_date'   => $latest?->created_at,
                'status'        => $s->status,
                'level_id'      => $sec?->level_id,
                'first_year_id' => $first?->classSection?->semester?->year_id,
            ];
        });

        // นักเรียนใหม่ = เข้าห้องครั้งแรกในปีการศึกษาปัจจุบัน (ถ้าไม่มีปีปัจจุบันตั้งไว้ ให้แสดงทั้งหมด)
        if ($currentYearId) {
            $rows = $rows->filter(fn($r) => (string) $r->first_year_id === (string) $currentYearId)->values();
        }

        // กรองตามระดับชั้นของห้องล่าสุด
        if ($levelId !== '') {
            $rows = $rows->filter(fn($r) => (string) $r->level_id === (string) $levelId)->values();
        }

        // เรียงตามวันที่เข้าเรียนล่าสุด (ใหม่สุดก่อน)
        $rows = $rows->sortByDesc(fn($r) => $r->enroll_date?->timestamp ?? 0)->values();

        return view('student.new_students_report', compact('rows', 'levels', 'levelId', 'search'));
    }
}