<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Promotion;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;

class StudentAlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with([
            'student',
            'fromSection.level',
            'fromSection.semester.academicYear',
        ])
        ->whereIn('promo_type', ['บันทึกจบ', 'ลาออก'])
        ->orderBy('promo_date', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('student', function ($q) use ($s) {
                $q->where('student_code', 'like', "%{$s}%")
                  ->orWhere('thai_firstname', 'like', "%{$s}%")
                  ->orWhere('thai_lastname', 'like', "%{$s}%");
            });
        }

        if ($request->filled('promo_type')) {
            $query->where('promo_type', $request->promo_type);
        }

        if ($request->filled('year')) {
            $query->whereHas('fromSection.semester.academicYear', function ($q) use ($request) {
                $q->where('year_name', 'like', '%' . $request->year . '%');
            });
        }

        $alumni = $query->paginate(20)->withQueryString();

        return view('student.student_alumni_index', compact('alumni'));
    }

    // ===== หน้านำเข้าศิษย์เก่า =====
    public function importIndex(Request $request)
    {
        $yearId     = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId    = $request->get('level_id');
        $sectionId  = $request->get('section_id');
        $search     = $request->get('search', '');

        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $levels        = Level::orderBy('sort_order')->get();

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        $sections = ($yearId && $levelId)
            ? ClassSection::with('level')
                ->where('level_id', $levelId)
                ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
                ->get()
            : collect();

        // ดึงศิษย์เก่า (บันทึกจบ/ลาออก)
        $query = Promotion::with(['student', 'fromSection.level', 'fromSection.semester.academicYear'])
            ->whereIn('promo_type', ['บันทึกจบ', 'ลาออก']);

        if ($yearId) {
            $query->whereHas('fromSection.semester', fn($q) => $q->where('year_id', $yearId));
        }
        if ($semesterId) {
            $query->whereHas('fromSection', fn($q) => $q->where('semester_id', $semesterId));
        }
        if ($levelId) {
            $query->whereHas('fromSection', fn($q) => $q->where('level_id', $levelId));
        }
        if ($sectionId) {
            $query->where('from_section_id', $sectionId);
        }
        if ($search) {
            $query->whereHas('student', fn($q) => $q
                ->where('student_code', 'like', "%{$search}%")
                ->orWhere('thai_firstname', 'like', "%{$search}%")
                ->orWhere('thai_lastname', 'like', "%{$search}%")
            );
        }

        $alumni = $query->orderByDesc('promo_date')->paginate(50)->withQueryString();

        // สำหรับ dropdown ระบุห้องใหม่
        $newLevels   = Level::orderBy('sort_order')->get();
        $allSections = ClassSection::with(['level', 'semester.academicYear'])
            ->whereHas('semester', fn($q) => $q->where('is_current', true))
            ->get()
            ->groupBy('level_id');

        return view('student.student_alumni_import', compact(
            'alumni', 'academicYears', 'levels', 'semesters', 'sections',
            'yearId', 'semesterId', 'levelId', 'sectionId', 'search',
            'newLevels', 'allSections'
        ));
    }

    public function importStore(Request $request)
    {
        $studentIds   = $request->input('student_ids', []);
        $newSectionIds = $request->input('new_section_id', []);

        if (empty($studentIds)) {
            return back()->with('error', 'กรุณาเลือกนักเรียนอย่างน้อย 1 คน');
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($studentIds as $studentId) {
            $newSectionId = $newSectionIds[$studentId] ?? null;
            if (!$newSectionId) { $skipped++; continue; }

            // ตรวจสอบว่าอยู่ในห้องนี้แล้วหรือยัง
            $exists = StudentSection::where('student_id', $studentId)
                ->where('section_id', $newSectionId)->exists();

            if ($exists) { $skipped++; continue; }

            $maxNum = StudentSection::where('section_id', $newSectionId)->max('student_number') ?? 0;

            StudentSection::create([
                'student_id'     => $studentId,
                'section_id'     => $newSectionId,
                'student_number' => $maxNum + 1,
                'status'         => 'active',
            ]);

            $imported++;
        }

        $msg = "นำเข้าสำเร็จ {$imported} คน";
        if ($skipped) $msg .= " (ข้าม {$skipped} คน เพราะไม่ได้ระบุห้องหรืออยู่ในห้องนั้นแล้ว)";

        return back()->with('success', $msg);
    }
}