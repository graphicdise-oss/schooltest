<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Promotion;
use App\Models\Student;
use Illuminate\Http\Request;

class PorPor3Controller extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();
        $currentSem    = Semester::where('is_current', true)->with('academicYear')->first();

        $yearId    = $request->year_id  ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $term      = $request->term     ?? ($currentSem->semester_name ?? '1');
        $levelId   = $request->level_id;

        $semester   = Semester::where('year_id', $yearId)->where('semester_name', $term)->first();
        $semesterId = $semester?->semester_id;

        $levels = Level::whereHas('classSections', fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('sort_order')->get();

        // ดึงนักเรียนที่สำเร็จการศึกษา (promo_type = บันทึกจบ) จาก section ของปีการศึกษา + ระดับชั้น
        $query = Promotion::with(['student', 'fromSection.level', 'fromSection.semester.academicYear'])
            ->where('promo_type', 'บันทึกจบ')
            ->whereHas('fromSection.semester', fn($q) => $q->where('year_id', $yearId));

        if ($levelId) {
            $query->whereHas('fromSection', fn($q) => $q->where('level_id', $levelId));
        }

        $promotions = $query->orderBy('promo_date')->get();

        // รวบรวมนักเรียนไม่ซ้ำ พร้อม section ล่าสุด
        $students = $promotions->map(fn($p) => [
            'student'      => $p->student,
            'section'      => $p->fromSection,
            'promo_date'   => $p->promo_date,
        ])->unique(fn($r) => $r['student']?->student_id ?? '')->values()->filter(fn($r) => $r['student']);

        return view('academic.por3_index', compact(
            'academicYears', 'levels', 'students',
            'yearId', 'term', 'levelId', 'semesterId'
        ));
    }
}
