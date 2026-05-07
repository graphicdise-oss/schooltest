<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Pp2Setting;
use App\Models\Pp2SectionSetting;
use Illuminate\Http\Request;

class Pp2Controller extends Controller
{
    public function index(Request $request)
    {
        $settings = Pp2Setting::first();

        // โหลดทุกปีการศึกษา → semester → ห้องเรียน พร้อม pp2_section_settings
        $academicYears = AcademicYear::with([
            'semesters.classSections.level',
            'semesters.classSections.pp2SectionSetting',
        ])->orderBy('year_id', 'desc')->get();

        $studentSections = collect();
        if ($request->filled('search')) {
            $s = $request->search;
            $studentSections = StudentSection::with(['student', 'classSection.level'])
                ->whereHas('student', fn($q) =>
                    $q->where('student_code', 'like', "%{$s}%")
                      ->orWhere('thai_firstname', 'like', "%{$s}%")
                      ->orWhere('thai_lastname', 'like', "%{$s}%")
                )
                ->get();
        }

        return view('student.pp2_index', compact('settings', 'academicYears', 'studentSections'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'school_name'   => 'nullable|string|max:255',
            'province'      => 'nullable|string|max:255',
            'affiliation'   => 'nullable|string|max:255',
            'director_name' => 'nullable|string|max:255',
        ]);

        $settings = Pp2Setting::first();
        if ($settings) {
            $settings->update($data);
        } else {
            Pp2Setting::create($data);
        }

        return redirect()->route('pp2.index')->with('success', 'บันทึกการตั้งค่าเรียบร้อย');
    }

    public function saveSectionDate(Request $request, $sectionId)
    {
        $request->validate([
            'issued_date' => 'nullable|date',
        ]);

        Pp2SectionSetting::updateOrCreate(
            ['section_id' => $sectionId],
            ['issued_date' => $request->issued_date ?: null]
        );

        return back()->with('success_section', 'บันทึกวันที่ออกเอกสารเรียบร้อย');
    }

    public function print($studentSectionId)
    {
        $studentSection = StudentSection::with([
            'student',
            'classSection.level',
            'classSection.pp2SectionSetting',
        ])->findOrFail($studentSectionId);

        $settings = Pp2Setting::first();

        // ใช้วันที่จากการตั้งค่าของห้องนั้น ถ้าไม่มีใช้วันปัจจุบัน
        $sectionDate = $studentSection->classSection?->pp2SectionSetting?->issued_date;
        $doc = $sectionDate ? (object)['issued_date' => $sectionDate, 'doc_number' => null] : null;

        return view('student.pp2_print', compact('studentSection', 'settings', 'doc'));
    }
}
