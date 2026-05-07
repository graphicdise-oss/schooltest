<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;

class Pp2Controller extends Controller
{
    private array $graduatingLevels = ['ป.6', 'ม.3', 'ม.6'];

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $yearId        = $request->input('year_id');
        $semesterId    = $request->input('semester_id');
        $levelId       = $request->input('level_id');
        $sectionId     = $request->input('section_id');
        $search        = $request->input('search');

        if (!$request->has('year_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
            $yearId = $currentYear?->year_id;
        }

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        $levels = Level::whereIn('name', $this->graduatingLevels)->orderBy('sort_order')->get();

        $sections = ($yearId && $levelId)
            ? ClassSection::where('level_id', $levelId)
                ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                ->with('level')
                ->orderBy('section_number')
                ->get()
            : collect();

        $studentSections = collect();
        if ($sectionId) {
            $query = StudentSection::with(['student', 'classSection.level'])
                ->where('section_id', $sectionId);

            if ($search) {
                $query->whereHas('student', fn($q) => $q
                    ->where('student_code', 'like', "%$search%")
                    ->orWhere('thai_firstname', 'like', "%$search%")
                    ->orWhere('thai_lastname', 'like', "%$search%")
                );
            }

            $studentSections = $query->orderBy('student_number')->get();

            $studentIds = $studentSections->pluck('student_id');
            $docs = Pp2Document::where('section_id', $sectionId)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');

            $studentSections = $studentSections->map(function ($ss) use ($docs) {
                $ss->pp2_doc = $docs->get($ss->student_id);
                return $ss;
            });
        }

        return view('academic.pp2_index', compact(
            'academicYears', 'semesters', 'levels', 'sections',
            'yearId', 'semesterId', 'levelId', 'sectionId', 'search',
            'studentSections'
        ));
    }

    public function setDocNumber(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|integer',
            'section_id'  => 'required|integer',
            'doc_number'  => 'required|string|max:100',
            'issued_date' => 'required|date',
        ]);

        Pp2Document::updateOrCreate(
            ['student_id' => $request->student_id, 'section_id' => $request->section_id],
            ['doc_number' => $request->doc_number, 'issued_date' => $request->issued_date]
        );

        return back()->with('success', 'บันทึกเลขที่เอกสารสำเร็จ');
    }

    public function print($studentId, $sectionId)
    {
        $studentSection = StudentSection::with([
            'student',
            'classSection.level',
            'classSection.semester.academicYear',
        ])->where('student_id', $studentId)
          ->where('section_id', $sectionId)
          ->firstOrFail();

        $doc = Pp2Document::where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->first();

        return view('academic.pp2_print', compact('studentSection', 'doc'));
    }
}
