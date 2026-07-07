<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Personne\Personnel;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Holiday;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== ปี/ภาคการศึกษาปัจจุบัน =====
        $currentYear     = AcademicYear::current();
        $currentSemester = Semester::current();
        if ($currentSemester) {
            $currentSemester->loadMissing('academicYear');
        }

        // ===== การ์ดสรุปหลัก =====
        $studentsStudying = Student::where('status', 'กำลังศึกษา')->count();
        $personnelWorking = Personnel::where('status', 'ปฏิบัติงาน')->count();

        // ===== วันเริ่ม/สิ้นสุด + วันคงเหลือของภาคเรียน =====
        $semStart   = $currentSemester?->start_date;
        $semEnd     = $currentSemester?->end_date;
        $daysTotal  = ($semStart && $semEnd) ? $semStart->diffInDays($semEnd) + 1 : null;
        $daysLeft   = null;
        if ($semEnd) {
            $today    = now()->startOfDay();
            $daysLeft = $today->lte($semEnd) ? $today->diffInDays($semEnd) : 0;
        }

        // ===== วันหยุดทั้งปีการศึกษา (ของปีการศึกษาปัจจุบัน) =====
        $holidays = $currentYear
            ? Holiday::where('year_id', $currentYear->year_id)->orderBy('start_date')->get()
            : collect();
        $holidayDays = $holidays->sum('day_count');

        // แผนที่วันที่ -> ชื่อวันหยุด (กระจายช่วงวันหยุดหลายวันออกเป็นรายวัน) สำหรับปฏิทิน
        $holidayMap = [];
        foreach ($holidays as $h) {
            if (!$h->start_date) continue;
            $d   = $h->start_date->copy();
            $end = $h->end_date ?? $h->start_date;
            while ($d->lte($end)) {
                $holidayMap[$d->format('Y-m-d')] = $h->title;
                $d->addDay();
            }
        }

        // เดือนเริ่มต้นของปฏิทิน: เดือนของวันหยุดแรก ถ้าไม่มีใช้เดือนปัจจุบัน
        $calDate  = $holidays->isNotEmpty() ? $holidays->first()->start_date : now();
        $calYear  = (int) $calDate->format('Y');
        $calMonth = (int) $calDate->format('n');

        // ===== คะแนนเต็มความประพฤติ (จาก config) =====
        $conductFullScore = config('school.conduct_full_score', 100);

        // ===== นักเรียนแยกตามระดับชั้น (ของภาคเรียนปัจจุบัน) =====
        $levels        = Level::orderBy('sort_order')->get();
        $studentsByLevel = collect();
        $enrolledStudentIds = collect();

        if ($currentSemester) {
            $sectionIds = ClassSection::where('semester_id', $currentSemester->semester_id)
                ->pluck('section_id');

            // รายชื่อ student_id ที่ถูกจัดเข้าห้องในเทอมนี้แล้ว
            $enrolledStudentIds = StudentSection::whereIn('section_id', $sectionIds)
                ->pluck('student_id')->unique();

            $rows = StudentSection::whereIn('student_sections.section_id', $sectionIds)
                ->join('students', 'student_sections.student_id', '=', 'students.student_id')
                ->join('class_sections', 'student_sections.section_id', '=', 'class_sections.section_id')
                ->select(
                    'class_sections.level_id',
                    DB::raw("SUM(CASE WHEN students.gender = 'ชาย' THEN 1 ELSE 0 END) as male"),
                    DB::raw("SUM(CASE WHEN students.gender = 'หญิง' THEN 1 ELSE 0 END) as female"),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('class_sections.level_id')
                ->get()
                ->keyBy('level_id');

            $studentsByLevel = $levels->map(function ($lv) use ($rows) {
                $r = $rows->get($lv->level_id);
                return (object) [
                    'level_name' => $lv->name,
                    'male'       => (int) ($r->male ?? 0),
                    'female'     => (int) ($r->female ?? 0),
                    'total'      => (int) ($r->total ?? 0),
                ];
            })->filter(fn($r) => $r->total > 0)->values();
        }

        $enrolledTotal = $studentsByLevel->sum('total');

        // ===== นักเรียนที่ยังไม่เปิดเทอม (กำลังศึกษา แต่ยังไม่ถูกจัดเข้าห้องเทอมนี้) =====
        $studentsNotOpened = Student::where('status', 'กำลังศึกษา')
            ->when($enrolledStudentIds->isNotEmpty(),
                fn($q) => $q->whereNotIn('student_id', $enrolledStudentIds))
            ->count();

        // ===== ภาพรวมระบบ =====
        $overview = [
            'students_total'  => Student::count(),
            'sections_total'  => $currentSemester
                ? ClassSection::where('semester_id', $currentSemester->semester_id)->count()
                : 0,
            'personnel_total' => Personnel::count(),
            'levels_total'    => $levels->count(),
        ];

        return view('dashboard.dashboard', compact(
            'currentYear', 'currentSemester',
            'studentsStudying', 'personnelWorking',
            'semStart', 'semEnd', 'daysTotal', 'daysLeft',
            'holidays', 'holidayDays', 'holidayMap', 'calYear', 'calMonth', 'conductFullScore',
            'studentsByLevel', 'enrolledTotal', 'studentsNotOpened',
            'overview'
        ));
    }
}
