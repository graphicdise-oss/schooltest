<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\StudentSection;
use App\Models\Academic\ClassAttendance;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // เลือกวิชา-ห้องที่จะเช็คชื่อ
    public function index(Request $request)
    {
        $semesterId  = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $subjectId   = $request->subject_id;
        $personnelId = $request->personnel_id;

        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();
        $subjects  = Subject::where('is_active', true)->orderBy('code')->get();
        $teachers  = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();

        $query = TeachingAssign::with(['personnel', 'subject', 'classSection.level'])
            ->where('semester_id', $semesterId)
            ->orderBy('section_id');

        if ($subjectId)   $query->where('subject_id', $subjectId);
        if ($personnelId) $query->where('personnel_id', $personnelId);

        $assigns = $query->get()->map(function ($a) {
            $a->attendance_days = ClassAttendance::where('assign_id', $a->assign_id)
                ->distinct('class_date')->count('class_date');
            return $a;
        });

        return view('academic.attendance_index', compact(
            'assigns', 'semesters', 'subjects', 'teachers', 'semesterId', 'subjectId', 'personnelId'
        ));
    }

    // หน้าเช็คชื่อของวิชา-ห้องนั้น (เลือกวันที่)
    public function mark(Request $request, $assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'classSection.semester.academicYear'])
            ->findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }

        $semester = $assign->classSection->semester;
        $date = $request->get('date', now()->toDateString());
        if ($semester->start_date && $date < $semester->start_date->toDateString()) $date = $semester->start_date->toDateString();
        if ($semester->end_date && $date > $semester->end_date->toDateString())   $date = $semester->end_date->toDateString();

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $existing = ClassAttendance::where('assign_id', $assignId)
            ->where('class_date', $date)
            ->get()->keyBy('student_id');

        $recentDates = ClassAttendance::where('assign_id', $assignId)
            ->selectRaw('class_date, count(*) as total')
            ->groupBy('class_date')
            ->orderByDesc('class_date')
            ->limit(10)
            ->get();

        return view('academic.attendance_mark', compact('assign', 'students', 'date', 'existing', 'recentDates'));
    }

    public function store(Request $request, $assignId)
    {
        $assign = TeachingAssign::findOrFail($assignId);

        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('attendance.index')->with('error', 'คุณไม่มีสิทธิ์เช็คชื่อวิชานี้');
        }

        $request->validate(['date' => 'required|date']);
        $statuses = $request->input('status', []);

        foreach ($statuses as $studentId => $status) {
            if (!in_array($status, ClassAttendance::STATUSES, true)) continue;
            ClassAttendance::updateOrCreate(
                ['assign_id' => $assignId, 'student_id' => $studentId, 'class_date' => $request->date],
                ['status' => $status]
            );
        }

        return redirect()
            ->route('attendance.mark', ['assign' => $assignId, 'date' => $request->date])
            ->with('success', 'บันทึกการเช็คชื่อสำเร็จ');
    }
}
