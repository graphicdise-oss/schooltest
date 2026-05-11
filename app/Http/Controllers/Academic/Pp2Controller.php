<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\Pp2Setting;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;
use App\Models\Personne\Personnel;

class Pp2Controller extends Controller
{
    private $graduatingLevels = ['ป.6', 'ม.3', 'ม.6'];

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $yearId = $request->get('year_id');
        $semesterId = $request->get('semester_id');
        $levelId = $request->get('level_id');
        $sectionId = $request->get('section_id');
        $search = $request->get('search', '');

        if (!$request->has('year_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
            $yearId = $currentYear?->year_id;
        }

        $semesters = $yearId
            ? Semester::where('year_id', $yearId)->orderBy('semester_name')->get()
            : collect();

        if (!$semesterId && $yearId) {
            $defaultSem = Semester::where('year_id', $yearId)->where('is_current', true)->first()
                ?? Semester::where('year_id', $yearId)->orderBy('semester_name')->first();
            $semesterId = $defaultSem?->semester_id;
        }

        $levels = Level::whereIn('name', $this->graduatingLevels)
            ->orderBy('sort_order')->get();

        $sections = ($levelId && $yearId)
            ? ClassSection::with('level')
                ->where('level_id', $levelId)
                ->whereHas('semester', fn($q) => $q->where('year_id', $yearId))
                ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                ->get()
            : collect();

        $students = collect();

        if ($sectionId) {
            $query = StudentSection::with(['student'])
                ->where('section_id', $sectionId);

            if ($search) {
                $query->whereHas(
                    'student',
                    fn($q) => $q
                        ->where('thai_firstname', 'like', "%$search%")
                        ->orWhere('thai_lastname', 'like', "%$search%")
                        ->orWhere('student_code', 'like', "%$search%")
                );
            }

            $students = $query->orderBy('student_number')->get();

            $studentIds = $students->pluck('student_id');
            $docs = Pp2Document::whereIn('student_id', $studentIds)
                ->where('section_id', $sectionId)
                ->get()->keyBy('student_id');
        } else {
            $docs = collect();
        }

        $setting = Pp2Setting::getInstance();
        $settings = $setting; // ✅ เพิ่มบรรทัดนี้
        $directors = Personnel::where('position', 'ผู้อำนวยการโรงเรียน')
            ->orderBy('thai_firstname')
            ->get();


        return view('academic.pp2_index', compact(
            'academicYears',
            'semesters',
            'levels',
            'sections',
            'yearId',
            'semesterId',
            'levelId',
            'sectionId',
            'search',
            'students',
            'docs',
            'setting',
            'settings',
            'directors' // ✅ เพิ่มตรงนี้
        ));
    }

    public function saveSetting(Request $request)
    {
        $request->validate([
            'school_name' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:100',
            'affiliation' => 'nullable|string|max:255',
            'director_name' => 'nullable|string|max:100',
        ]);

        $setting = Pp2Setting::first();
        if ($setting) {
            $setting->update($request->only(['school_name', 'province', 'affiliation', 'director_name']));
        } else {
            Pp2Setting::create($request->only(['school_name', 'province', 'affiliation', 'director_name']));
        }

        return back()->with('success', 'บันทึกการตั้งค่าสำเร็จ');
    }

    public function setDocNumber(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'section_id' => 'required',
            'doc_number' => 'required',
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
            'classSection.pp2SectionSetting', // ✅ เพิ่ม
        ])
            ->where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->firstOrFail();

        $doc = Pp2Document::where('student_id', $studentId)
            ->where('section_id', $sectionId)->first();

        // ✅ ถ้า doc ไม่มีวัน ให้ใช้วันจาก section setting
        if (!$doc?->issued_date) {
            $sectionDate = $studentSection->classSection?->pp2SectionSetting?->issued_date;
            if ($sectionDate) {
                $doc = (object) [
                    'issued_date' => $sectionDate,
                    'doc_number' => $doc?->doc_number ?? null,
                ];
            }
        }

        $setting = Pp2Setting::getInstance();
        $settings = $setting;

        return view('academic.pp2_print', compact('studentSection', 'doc', 'setting', 'settings'));
    }

    public function saveSectionDate(Request $request, $sectionId)
    {
        $request->validate([
            'issued_date' => 'nullable|date',
        ]);

        \App\Models\Pp2SectionSetting::updateOrCreate(
            ['section_id' => $sectionId],
            ['issued_date' => $request->issued_date ?: null]
        );

        return redirect()->route('pp2.index', [
            'year_id' => $request->get('year_id'),
            'level_id' => $request->get('level_id'),
            'section_id' => $sectionId,
            'semester_id' => $request->get('semester_id'),
        ])->with('success_section', 'บันทึกวันที่เรียบร้อย');
    }
}