<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\TimetableSlot;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $levels = Level::orderBy('sort_order')->get();
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();
        $teachers = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();
        $subjects = Subject::where('is_active', true)->orderBy('code')->get();

        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->orderBy('level_id')->orderBy('section_number')->get();

        $assigns = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'timetableSlots'])
            ->where('semester_id', $semesterId)
            ->get();

        return view('academic.timetable', compact('assigns', 'sections', 'teachers', 'subjects', 'semesters', 'levels', 'semesterId'));
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
}