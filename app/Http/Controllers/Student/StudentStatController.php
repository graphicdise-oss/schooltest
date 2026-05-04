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
        $years    = AcademicYear::orderByDesc('year_name')->get();
        $yearId   = $request->input('year_id');
        $semesterId = $request->input('semester_id');
        $levelId  = $request->input('level_id');

        if (!$yearId) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $years->first();
            $yearId = $currentYear?->year_id;
        }

        $semesters = $yearId ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get() : collect();

        if (!$semesterId && $yearId) {
            $defaultSem = Semester::where('year_id', $yearId)->where('is_current', true)->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();
            if ($defaultSem) $semesterId = $defaultSem->semester_id;
        }

        $levels = Level::orderBy('sort_order')->get();

        $sectionIds = ClassSection::when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->pluck('section_id');

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

        $levelsWithStats = $levels->map(function ($lv) use ($studentStats) {
            $stat = $studentStats->get($lv->level_id);
            $lv->male_count   = $stat?->male_count ?? 0;
            $lv->female_count = $stat?->female_count ?? 0;
            $lv->total        = $stat?->total ?? 0;
            return $lv;
        });

        if ($levelId) {
            $levelsWithStats = $levelsWithStats->where('level_id', $levelId)->values();
        }

        $levelGroups = $levelsWithStats->where('total', '>', 0)->groupBy('level_group');
        $grandMale   = $levelsWithStats->sum('male_count');
        $grandFemale = $levelsWithStats->sum('female_count');
        $grandTotal  = $levelsWithStats->sum('total');

        return view('student.student_stat', compact(
            'years', 'semesters', 'levels',
            'yearId', 'semesterId', 'levelId',
            'levelsWithStats', 'levelGroups',
            'grandMale', 'grandFemale', 'grandTotal'
        ));
    }
}
