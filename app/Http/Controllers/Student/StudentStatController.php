<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentStatController extends Controller
{
    public function index(Request $request)
    {
        $yearId     = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId    = $request->get('level_id', '');
        $reportType = $request->get('report_type', 'gender');

        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        // auto-default เฉพาะตอนโหลดหน้าแรก (ไม่มี year_id ใน URL)
        if (!$request->has('year_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
            $yearId      = $currentYear?->year_id;
            $defaultSem  = Semester::where('year_id', $yearId)->where('is_current', true)->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();
            $semesterId  = $defaultSem?->semester_id;
        }

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        $levels = Level::orderBy('sort_order')->get();

        $stats = collect();

        if ($yearId) {
            // filter ห้องเรียนตามปี (และเทอมถ้าเลือก)
            $sectionQuery = ClassSection::with('level')
                ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                ->when($levelId, fn($q) => $q->where('level_id', $levelId))
                ->get();

            $roomCount  = $sectionQuery->groupBy('level_id')->map(fn($g) => $g->count());
            $sectionIds = $sectionQuery->pluck('section_id');

            $studentStats = StudentSection::whereIn('student_sections.section_id', $sectionIds)
                ->join('students', 'student_sections.student_id', '=', 'students.student_id')
                ->join('class_sections', 'student_sections.section_id', '=', 'class_sections.section_id')
                ->select(
                    'class_sections.level_id',
                    DB::raw("SUM(CASE WHEN students.gender = 'ชาย' THEN 1 ELSE 0 END) as male_count"),
                    DB::raw("SUM(CASE WHEN students.gender = 'หญิง' THEN 1 ELSE 0 END) as female_count"),
                    DB::raw('COUNT(student_sections.id) as total')
                )
                ->groupBy('class_sections.level_id')
                ->get()
                ->keyBy('level_id');

            $levelMap = $levels->keyBy('level_id');

            foreach ($sectionQuery->pluck('level_id')->unique() as $lvId) {
                $lv  = $levelMap->get($lvId);
                $stu = $studentStats->get($lvId);
                $stats->push([
                    'level_id'    => $lvId,
                    'level_name'  => $lv?->name ?? '-',
                    'level_group' => $lv?->level_group ?? 'อื่นๆ',
                    'sort_order'  => $lv?->sort_order ?? 99,
                    'rooms'       => $roomCount->get($lvId, 0),
                    'male'        => $stu?->male_count ?? 0,
                    'female'      => $stu?->female_count ?? 0,
                    'total'       => $stu?->total ?? 0,
                ]);
            }

            $stats = $stats->sortBy('sort_order');
        }

        $grouped = $stats->groupBy('level_group');

        return view('student.student_stat', compact(
            'academicYears', 'semesters', 'levels',
            'yearId', 'semesterId', 'levelId',
            'reportType', 'grouped'
        ));
    }
}