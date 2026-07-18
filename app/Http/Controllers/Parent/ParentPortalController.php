<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Semester;
use App\Models\Academic\TeachingAssign;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentPortalController extends Controller
{
    private function currentSection($student)
    {
        return $student->studentSections()
            ->with('classSection.level', 'classSection.homeroomTeacher')
            ->where('status', 'กำลังศึกษา')
            ->latest('id')
            ->first();
    }

    public function dashboard()
    {
        $student = Auth::guard('parent')->user();
        $studentSection = $this->currentSection($student);

        return view('parent.dashboard', compact('student', 'studentSection'));
    }

    public function grades(Request $request)
    {
        $student = Auth::guard('parent')->user();

        $semesterIds = FinalGrade::where('student_id', $student->student_id)
            ->pluck('semester_id')->unique();
        $semesters = Semester::with('academicYear')
            ->whereIn('semester_id', $semesterIds)
            ->orderByDesc('semester_id')->get();

        $semesterId = $request->semester_id ?? $semesters->first()?->semester_id;

        $rows = collect();
        $gpa = 0;
        $totalCredits = 0;

        if ($semesterId) {
            $grades = FinalGrade::with('teachingAssign.subject')
                ->where('student_id', $student->student_id)
                ->where('semester_id', $semesterId)
                ->get();

            $rows = $grades->map(function ($g) {
                $subj = $g->teachingAssign->subject ?? null;
                $isActivity = ($subj->subject_group ?? '') === 'กิจกรรมพัฒนาผู้เรียน';
                return (object) [
                    'code'        => $subj->code ?? '-',
                    'name'        => $subj->name_th ?? '-',
                    'credits'     => (float) ($subj->credits ?? 0),
                    'grade'       => $g->grade,
                    'gpa_point'   => (float) ($g->gpa_point ?? 0),
                    'is_activity' => $isActivity,
                ];
            });

            $creditSum = 0.0;
            $pointSum = 0.0;
            foreach ($rows as $r) {
                if ($r->is_activity) continue;
                $creditSum += $r->credits;
                $pointSum  += $r->credits * $r->gpa_point;
                $totalCredits += $r->credits;
            }
            $gpa = $creditSum > 0 ? round($pointSum / $creditSum, 2) : 0;
        }

        return view('parent.grades', compact('student', 'semesters', 'semesterId', 'rows', 'gpa', 'totalCredits'));
    }

    private function buildTimetableGrid($student)
    {
        $studentSection = $this->currentSection($student);
        $section = $studentSection?->classSection;

        $days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];

        $dayStartHour = 6;
        $dayEndHour   = 18;
        $units = [];
        for ($h = $dayStartHour; $h < $dayEndHour; $h++) {
            $units[] = sprintf('%02d:00', $h);
            $units[] = sprintf('%02d:30', $h);
        }
        $baseMinutes = $dayStartHour * 60;

        $slotGrid = [];
        $assigns = collect();

        if ($section) {
            $assigns = TeachingAssign::with(['personnel', 'subject', 'timetableSlots'])
                ->where('section_id', $section->section_id)
                ->where('semester_id', $section->semester_id)
                ->get();

            foreach ($assigns as $assign) {
                foreach ($assign->timetableSlots as $slot) {
                    $start = \Carbon\Carbon::parse($slot->start_time);
                    $end = \Carbon\Carbon::parse($slot->end_time);
                    $unitIndex = (int) round((($start->hour * 60 + $start->minute) - $baseMinutes) / 30);
                    $span = max(1, (int) round($start->diffInMinutes($end) / 30));
                    if ($unitIndex >= 0 && $unitIndex < count($units)) {
                        $span = min($span, count($units) - $unitIndex);
                        $slotGrid[$slot->day_of_week][$unitIndex] = ['slot' => $slot, 'assign' => $assign, 'span' => $span];
                    }
                }
            }
        }

        return compact('studentSection', 'section', 'days', 'units', 'slotGrid', 'assigns');
    }

    public function timetable()
    {
        $student = Auth::guard('parent')->user();
        $grid = $this->buildTimetableGrid($student);

        return view('parent.timetable', array_merge($grid, compact('student')));
    }

    public function timetablePrint()
    {
        $student = Auth::guard('parent')->user();
        $grid = $this->buildTimetableGrid($student);

        return view('parent.timetable_print', array_merge($grid, compact('student')));
    }

    public function calendar(Request $request)
    {
        $student = Auth::guard('parent')->user();
        $currentYear = AcademicYear::current();

        $holidays = $currentYear
            ? Holiday::where('year_id', $currentYear->year_id)->orderBy('start_date')->get()
            : collect();

        $holidayMap = [];
        foreach ($holidays as $h) {
            if (!$h->start_date) continue;
            $d = $h->start_date->copy();
            $end = $h->end_date ?? $h->start_date;
            while ($d->lte($end)) {
                $holidayMap[$d->format('Y-m-d')] = $h->title;
                $d->addDay();
            }
        }

        $month = (int) ($request->month ?? now()->format('n'));
        $year  = (int) ($request->year ?? now()->format('Y'));

        return view('parent.calendar', compact('student', 'holidays', 'holidayMap', 'month', 'year'));
    }

    public function contact()
    {
        $student = Auth::guard('parent')->user();
        $studentSection = $this->currentSection($student);
        $teacher = $studentSection?->classSection?->homeroomTeacher;

        return view('parent.contact', compact('student', 'studentSection', 'teacher'));
    }

    public function changePasswordForm()
    {
        $student = Auth::guard('parent')->user();
        return view('parent.change_password', compact('student'));
    }

    public function changePassword(Request $request)
    {
        $student = Auth::guard('parent')->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'กรุณากรอกรหัสผ่านเดิม',
            'new_password.required'     => 'กรุณากรอกรหัสผ่านใหม่',
            'new_password.min'          => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร',
            'new_password.confirmed'    => 'ยืนยันรหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        if (!Hash::check($request->current_password, $student->parent_password)) {
            return back()->with('error', 'รหัสผ่านเดิมไม่ถูกต้อง');
        }

        $student->update([
            'parent_password'         => Hash::make($request->new_password),
            'parent_password_changed' => true,
        ]);

        return back()->with('success', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว');
    }
}
