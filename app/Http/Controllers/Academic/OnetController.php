<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\OnetScore;
use Illuminate\Http\Request;

class OnetController extends Controller
{
    public function index(Request $request)
    {
        $years  = AcademicYear::orderByDesc('year_name')->get();
        $yearId = $request->get('year_id') ?? optional(AcademicYear::current())->year_id ?? optional($years->first())->year_id;

        $levels  = Level::orderBy('sort_order')->get();
        $levelId = $request->get('level_id', '');

        $sections = collect();
        if ($yearId && $levelId !== '') {
            $sections = ClassSection::where('level_id', $levelId)
                ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
                ->orderBy('section_number')
                ->get();
        }

        $sectionId = $request->get('section_id');
        $students  = collect();
        $scores    = collect();

        if ($sectionId) {
            $students = StudentSection::where('student_sections.section_id', $sectionId)
                ->join('students', 'student_sections.student_id', '=', 'students.student_id')
                ->orderBy('student_sections.student_number')
                ->select('students.*', 'student_sections.student_number')
                ->get();

            $scores = OnetScore::where('year_id', $yearId)
                ->whereIn('student_id', $students->pluck('student_id'))
                ->get()
                ->groupBy('student_id')
                ->map(fn($rows) => $rows->keyBy('subject'));
        }

        return view('academic.onet_index', compact(
            'years', 'yearId', 'levels', 'levelId', 'sections', 'sectionId', 'students', 'scores'
        ));
    }

    public function save(Request $request)
    {
        $yearId  = $request->input('year_id');
        $levelId = $request->input('level_id');
        $rows    = $request->input('scores', []);

        foreach ($rows as $studentId => $subjects) {
            foreach ($subjects as $subject => $score) {
                if ($score === '' || $score === null) continue;
                OnetScore::updateOrCreate(
                    ['student_id' => $studentId, 'year_id' => $yearId, 'subject' => $subject],
                    ['level_id' => $levelId, 'score' => $score]
                );
            }
        }

        return redirect()
            ->route('onet.index', ['year_id' => $yearId, 'level_id' => $levelId, 'section_id' => $request->input('section_id')])
            ->with('success', 'บันทึกคะแนน O-NET สำเร็จ');
    }
}
