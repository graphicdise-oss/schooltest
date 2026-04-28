<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Semester;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\StudentDocNumber;
use App\Models\Student;
use Illuminate\Http\Request;

class PorPor1Controller extends Controller
{
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $sectionId  = $request->section_id;
        $search     = $request->search;

        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();

        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')
            ->get();

        $students = collect();
        $currentSection = null;

        if ($sectionId) {
            $currentSection = ClassSection::with('level')->find($sectionId);
            $rows = StudentSection::with(['student'])
                ->where('section_id', $sectionId)
                ->where('status', 'กำลังศึกษา')
                ->orderBy('student_number')
                ->get();
            $students = $rows->map(fn($ss) => $ss->student)->filter();
        } elseif ($search) {
            $students = Student::where('status', 'กำลังศึกษา')
                ->where(fn($q) => $q
                    ->where('student_code', 'like', "%$search%")
                    ->orWhere('thai_firstname', 'like', "%$search%")
                    ->orWhere('thai_lastname', 'like', "%$search%")
                )->orderBy('thai_firstname')->get();
        }

        // โหลด doc numbers ที่มีอยู่แล้ว
        $docNumbers = [];
        if ($students->count() && $semesterId) {
            $ids = $students->pluck('student_id');
            StudentDocNumber::whereIn('student_id', $ids)
                ->where('semester_id', $semesterId)
                ->get()
                ->each(fn($d) => $docNumbers[$d->student_id] = $d);
        }

        return view('academic.por1_index', compact(
            'semesters', 'sections', 'students', 'docNumbers',
            'semesterId', 'sectionId', 'search', 'currentSection'
        ));
    }

    public function setDocNumber(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|integer',
            'semester_id' => 'required|integer',
            'level_id'    => 'required|integer',
            'doc_set'     => 'nullable|string|max:20',
            'doc_number'  => 'nullable|string|max:20',
        ]);

        StudentDocNumber::updateOrCreate(
            ['student_id' => $request->student_id, 'semester_id' => $request->semester_id],
            [
                'level_id'   => $request->level_id,
                'doc_set'    => $request->doc_set,
                'doc_number' => $request->doc_number,
            ]
        );

        return redirect()->back()->with('success', 'บันทึกเลขที่เอกสารสำเร็จ');
    }

    public function bulkSetDocSet(Request $request)
    {
        $request->validate([
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
            'doc_set'     => 'required|string|max:20',
        ]);

        $section = ClassSection::findOrFail($request->section_id);

        $rows = StudentSection::with('student')
            ->where('section_id', $request->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        foreach ($rows as $i => $ss) {
            StudentDocNumber::updateOrCreate(
                ['student_id' => $ss->student_id, 'semester_id' => $request->semester_id],
                [
                    'level_id'   => $section->level_id,
                    'doc_set'    => $request->doc_set,
                    'doc_number' => str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                ]
            );
        }

        return redirect()->back()->with('success', 'ตั้งเลขชุดทั้งห้องสำเร็จ (' . $rows->count() . ' คน)');
    }
}
