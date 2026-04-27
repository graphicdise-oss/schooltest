<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\TimetableSlot;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\Curriculum;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $levels     = Level::orderBy('sort_order')->get();
        $semesters  = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();

        $query = ClassSection::with(['level', 'homeroomTeacher', 'teachingAssigns.timetableSlots'])
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number');

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        $sections        = $query->get();
        $currentSemester = $semesters->firstWhere('semester_id', $semesterId);

        return view('academic.timetable_index', compact('sections', 'semesters', 'levels', 'semesterId', 'currentSemester'));
    }

    public function storeAssign(Request $request)
    {
        $request->validate(['personnel_id' => 'required', 'subject_id' => 'required', 'section_id' => 'required', 'semester_id' => 'required']);
        TeachingAssign::firstOrCreate($request->only(['personnel_id', 'subject_id', 'section_id', 'semester_id']));
        return redirect()->back()->with('success', 'มอบหมายการสอนสำเร็จ');
    }

    public function destroyAssign($id)
    {
        TeachingAssign::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบการมอบหมายสำเร็จ');
    }

    public function storeSlot(Request $request)
    {
        $request->validate(['assign_id' => 'required', 'day_of_week' => 'required', 'start_time' => 'required', 'end_time' => 'required']);
        TimetableSlot::create($request->only(['assign_id', 'day_of_week', 'start_time', 'end_time', 'room']));
        return redirect()->back()->with('success', 'เพิ่มคาบเรียนสำเร็จ');
    }

    public function updateSlot(Request $request, $id)
    {
        TimetableSlot::findOrFail($id)->update($request->only(['day_of_week', 'start_time', 'end_time', 'room']));
        return redirect()->back()->with('success', 'แก้ไขคาบเรียนสำเร็จ');
    }

    public function destroySlot($id)
    {
        TimetableSlot::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบคาบเรียนสำเร็จ');
    }

    // แสดงตารางสอนแบบตาราง (วัน x คาบ)
    public function viewTimetable(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $sectionId = $request->section_id;
        $teacherId = $request->teacher_id;

        $query = TimetableSlot::with(['teachingAssign.personnel', 'teachingAssign.subject', 'teachingAssign.classSection.level'])
            ->whereHas('teachingAssign', fn($q) => $q->where('semester_id', $semesterId));

        if ($sectionId) $query->whereHas('teachingAssign', fn($q) => $q->where('section_id', $sectionId));
        if ($teacherId) $query->whereHas('teachingAssign', fn($q) => $q->where('personnel_id', $teacherId));

        $slots = $query->orderBy('start_time')->get();

        $days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        $sections = ClassSection::with('level')->where('semester_id', $semesterId)->orderBy('level_id')->orderBy('section_number')->get();
        $teachers = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();

        return view('academic.timetable_view', compact('slots', 'days', 'sections', 'teachers', 'semesters', 'semesterId', 'sectionId', 'teacherId'));
    }

    public function sectionView($sectionId)
    {
        $section = ClassSection::with(['level', 'semester.academicYear', 'homeroomTeacher', 'curriculum'])->findOrFail($sectionId);

        $assigns = TeachingAssign::with(['personnel', 'subject', 'timetableSlots'])
            ->where('section_id', $sectionId)
            ->where('semester_id', $section->semester_id)
            ->get();

        $teachers = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $subjects = Subject::where('is_active', true)->orderBy('code')->get();
        $days     = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
        $hours    = range(6, 20);

        $slotGrid = [];
        foreach ($assigns as $assign) {
            foreach ($assign->timetableSlots as $slot) {
                $startH = (int) \Carbon\Carbon::parse($slot->start_time)->format('H');
                $endH   = (int) \Carbon\Carbon::parse($slot->end_time)->format('H');
                $span   = max(1, $endH - $startH);
                $slotGrid[$slot->day_of_week][$startH] = ['slot' => $slot, 'assign' => $assign, 'span' => $span];
            }
        }

        $curriculums = Curriculum::with(['curriculumSubjects.subject', 'curriculumSubjects.personnel'])
            ->where('level_id', $section->level_id)
            ->where('is_active', true)
            ->orderBy('year_applied', 'desc')
            ->get();

        return view('academic.timetable_section', compact(
            'section', 'assigns', 'teachers', 'subjects', 'days', 'hours', 'slotGrid', 'curriculums'
        ));
    }

    public function clearSection($sectionId)
    {
        $assignIds = TeachingAssign::where('section_id', $sectionId)->pluck('assign_id');
        TimetableSlot::whereIn('assign_id', $assignIds)->delete();
        return redirect()->back()->with('success', 'ล้างข้อมูลตารางสอนสำเร็จ');
    }

    public function importCurriculum(Request $request, $sectionId)
    {
        $section = ClassSection::findOrFail($sectionId);
        $personnelIds = $request->input('personnel_ids', []);

        if ($request->curriculum_id) {
            $section->update(['curriculum_id' => $request->curriculum_id]);
        }

        // ลบวิชาที่ไม่อยู่ในแผนใหม่ออก
        $newSubjectIds = array_keys($personnelIds);
        $removeAssigns = TeachingAssign::where('section_id', $sectionId)
            ->where('semester_id', $section->semester_id)
            ->whereNotIn('subject_id', $newSubjectIds)
            ->pluck('assign_id');
        TimetableSlot::whereIn('assign_id', $removeAssigns)->delete();
        TeachingAssign::whereIn('assign_id', $removeAssigns)->delete();

        // เพิ่มวิชาใหม่ที่ยังไม่มี หรืออัปเดตครูถ้ามีอยู่แล้ว
        foreach ($personnelIds as $subjectId => $personnelId) {
            if (!$personnelId) continue;
            TeachingAssign::firstOrCreate([
                'subject_id'  => $subjectId,
                'section_id'  => $sectionId,
                'semester_id' => $section->semester_id,
            ], ['personnel_id' => $personnelId]);
        }

        return redirect()->back()->with('success', 'นำเข้าวิชาจากแผนการเรียนสำเร็จ');
    }

    public function setCurriculum(Request $request, $sectionId)
    {
        ClassSection::findOrFail($sectionId)->update([
            'curriculum_id' => $request->curriculum_id ?: null
        ]);
        return redirect()->back()->with('success', 'เปลี่ยนแผนการเรียนสำเร็จ');
    }
}