<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\StudentSection;
use App\Models\Academic\ClassAttendance;
use App\Models\Academic\SubjectAssessment;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\ScoreCategory;
use App\Models\Academic\StudentScore;
use App\Models\Academic\TimetableSlot;
use App\Models\Holiday;
use App\Models\Academic\Pp2Setting;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class Por5Controller extends Controller
{
    private const QUALITY_LEVELS = ['ดีเยี่ยม (3)' => 'ดีเยี่ยม', 'ดี (2)' => 'ดี', 'ผ่าน (1)' => 'ผ่าน'];

    // เลือกวิชา-ห้องที่จะทำ ปพ.5
    public function index(Request $request)
    {
        $semesterId  = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $subjectId   = $request->subject_id;
        $personnelId = $request->personnel_id;

        $semesters = Semester::with('academicYear')->orderedByRecency()->get();
        $subjects  = Subject::where('is_active', true)->orderBy('code')->get();
        $teachers  = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();

        $query = TeachingAssign::with(['personnel', 'subject', 'classSection.level'])
            ->where('semester_id', $semesterId)
            ->orderBy('section_id');

        if ($subjectId)   $query->where('subject_id', $subjectId);
        if ($personnelId) $query->where('personnel_id', $personnelId);

        $assigns = $query->get();

        return view('academic.por5_index', compact(
            'assigns', 'semesters', 'subjects', 'teachers', 'semesterId', 'subjectId', 'personnelId'
        ));
    }

    // กรอกผลประเมินคุณภาพผู้เรียนรายวิชา (3 ด้าน) สำหรับ ปพ.5
    public function manage($assignId)
    {
        $assign = $this->authorizedAssign($assignId);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $assessments = SubjectAssessment::where('assign_id', $assignId)
            ->get()->keyBy('student_id');

        return view('academic.por5_manage', compact('assign', 'students', 'assessments'));
    }

    public function saveAssessment(Request $request, $assignId)
    {
        $assign = $this->authorizedAssign($assignId);
        $rows = $request->input('assess', []);

        foreach ($rows as $studentId => $vals) {
            $charItems = array_map(fn($i) => isset($vals['char'][$i]) && $vals['char'][$i] !== '' ? (int) $vals['char'][$i] : null, range(1, 8));
            $readItems = array_map(fn($i) => isset($vals['read'][$i]) && $vals['read'][$i] !== '' ? (int) $vals['read'][$i] : null, range(1, 5));

            $charLevel = SubjectAssessment::computeDesiredCharLevel($charItems);
            $readLevel = SubjectAssessment::computeReadingThinkingLevel($readItems);

            $data = ['competency' => $vals['competency'] ?: null];
            foreach (range(1, 8) as $i) $data["char_{$i}"] = $charItems[$i - 1];
            foreach (range(1, 5) as $i) $data["read_{$i}"] = $readItems[$i - 1];
            $data['desired_char'] = SubjectAssessment::levelLabel($charLevel) ?: null;
            $data['reading_thinking'] = SubjectAssessment::levelLabel($readLevel) ?: null;

            SubjectAssessment::updateOrCreate(
                ['assign_id' => $assignId, 'student_id' => $studentId],
                $data
            );
        }

        return redirect()->route('por5.manage', $assignId)->with('success', 'บันทึกผลการประเมินสำเร็จ');
    }

    // พิมพ์ ปพ.5 เต็มรูปแบบ
    public function print($assignId)
    {
        $assign = TeachingAssign::with([
            'personnel', 'subject', 'classSection.level', 'classSection.semester.academicYear',
        ])->findOrFail($assignId);

        $section  = $assign->classSection;
        $semester = $section->semester;

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $studentIds = $students->pluck('student.student_id');

        // ===== 1. การกระจายผลการเรียน =====
        $grades = FinalGrade::where('assign_id', $assignId)->get()->keyBy('student_id');
        $gradeBuckets = ['4', '3.5', '3', '2.5', '2', '1.5', '1', '0'];
        $gradeCount = array_fill_keys($gradeBuckets, 0);
        $specialBuckets = ['ร', 'มส', 'มก', 'ผ', 'มผ', 'อื่นๆ'];
        $specialCount = array_fill_keys($specialBuckets, 0);

        foreach ($students as $s) {
            $g = $grades->get($s->student_id);
            $val = $g->grade ?? null;
            if ($val !== null && in_array($val, $gradeBuckets, true)) {
                $gradeCount[$val]++;
            } elseif ($val !== null) {
                $specialCount[in_array($val, $specialBuckets, true) ? $val : 'อื่นๆ']++;
            }
        }
        $totalStudents = $students->count();
        $gradePct = [];
        foreach ($gradeCount as $k => $v) {
            $gradePct[$k] = $totalStudents ? round($v / $totalStudents * 100, 2) : 0;
        }

        // ===== 2. ผลการประเมินคุณภาพผู้เรียนรายวิชา (3 ด้าน) =====
        $subjectAssessments = SubjectAssessment::where('assign_id', $assignId)->get()->keyBy('student_id');
        $qualitySummary = [];
        foreach (['desired_char' => 'คุณลักษณะอันพึงประสงค์', 'reading_thinking' => 'การอ่านคิดวิเคราะห์และเขียน', 'competency' => 'สมรรถนะที่สำคัญของผู้เรียน'] as $field => $label) {
            $counts = ['ดีเยี่ยม' => 0, 'ดี' => 0, 'ผ่าน' => 0, 'ไม่ผ่าน' => 0];
            foreach ($students as $s) {
                $a = $subjectAssessments->get($s->student_id);
                $v = $a?->{$field};
                if ($v && isset($counts[$v])) $counts[$v]++;
            }
            $qualitySummary[$field] = [
                'label'  => $label,
                'counts' => $counts,
                'pct'    => collect($counts)->map(fn($c) => $totalStudents ? round($c / $totalStudents * 100, 2) : 0),
            ];
        }

        // ===== 3. วันเรียนตลอดภาคเรียน (จาก timetable + วันหยุด) =====
        $classDates = $this->buildClassDates($assign, $semester);
        $attendancePages = $this->paginateAttendanceGrid($this->buildAttendanceGrid($assign, $semester));

        // ===== 4. บันทึกเช็คชื่อ =====
        $attendance = ClassAttendance::where('assign_id', $assignId)->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->keyBy(fn($r) => $r->class_date->format('Y-m-d')));

        // สถิติเข้าเรียนรายคน
        $attendanceStats = $students->map(function ($s) use ($attendance, $classDates) {
            $rows = $attendance->get($s->student_id, collect());
            $present = $rows->where('status', 'มา')->count();
            $sick    = $rows->where('status', 'ป่วย')->count();
            $leave   = $rows->where('status', 'ลา')->count();
            $absent  = $rows->where('status', 'ขาด')->count();
            $total   = count($classDates);
            return (object) [
                'student' => $s->student,
                'student_number' => $s->student_number,
                'present' => $present, 'sick' => $sick, 'leave' => $leave, 'absent' => $absent,
                'pct' => $total ? round($present / $total * 100, 2) : 0,
            ];
        });

        // ===== 5. คะแนนเก็บ =====
        $categories = ScoreCategory::where('assign_id', $assignId)->orderBy('sort_order')->get();
        $scores = StudentScore::whereIn('category_id', $categories->pluck('category_id'))
            ->get()->groupBy('student_id')->map(fn($rows) => $rows->keyBy('category_id'));

        // ===== ข้อมูลโรงเรียน/ลายเซ็นผู้อนุมัติ (จากหน้า "ตั้งค่าเริ่มต้น") =====
        $school = Pp2Setting::mergedSchoolConfig();
        $schoolLogoPath = \App\Models\SchoolInfoSetting::getInstance()->logo_path;
        $subjectGroupHead = \App\Models\Academic\SubjectGroupHead::where('subject_group', $assign->subject->subject_group ?? null)
            ->first();

        $studentChunks = $students->chunk(45)->values();

        return view('academic.por5_print', compact(
            'assign', 'section', 'semester', 'students', 'studentChunks',
            'gradeCount', 'gradePct', 'specialCount', 'totalStudents',
            'qualitySummary', 'classDates', 'attendancePages', 'attendance', 'attendanceStats',
            'categories', 'scores', 'school', 'schoolLogoPath', 'subjectGroupHead', 'subjectAssessments'
        ));
    }

    // แบ่งตาราง เดือน>สัปดาห์>วันที่ ออกเป็นหลายหน้า หน้าละไม่เกิน ~20 คอลัมน์วันที่ (พอดีกระดาษแนวนอน)
    private function paginateAttendanceGrid(array $months, int $maxColsPerPage = 20): array
    {
        $pages = [];
        $currentPage = [];
        $currentCols = 0;

        foreach ($months as $month) {
            foreach ($month['weeks'] as $week) {
                $weekCols = count($week['dates']);
                if ($currentCols > 0 && $currentCols + $weekCols > $maxColsPerPage) {
                    $pages[] = $currentPage;
                    $currentPage = [];
                    $currentCols = 0;
                }
                $monthKey = $month['label'];
                if (!isset($currentPage[$monthKey])) {
                    $currentPage[$monthKey] = ['label' => $month['label'], 'weeks' => []];
                }
                $currentPage[$monthKey]['weeks'][] = $week;
                $currentCols += $weekCols;
            }
        }
        if ($currentCols > 0) $pages[] = $currentPage;

        return array_map('array_values', $pages);
    }

    private function authorizedAssign($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level'])->findOrFail($assignId);
        $user = auth()->user();
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }
        return $assign;
    }

    // สร้างรายการวันที่ที่มีการเรียนวิชานี้ตลอดภาคเรียน (จากตารางสอน หักวันหยุด)
    private function buildClassDates(TeachingAssign $assign, Semester $semester): array
    {
        if (!$semester->start_date || !$semester->end_date) return [];

        $daysOfWeek = TimetableSlot::where('assign_id', $assign->assign_id)
            ->pluck('day_of_week')->unique()->all();
        if (empty($daysOfWeek)) return [];

        $thaiDowMap = ['อาทิตย์' => 0, 'จันทร์' => 1, 'อังคาร' => 2, 'พุธ' => 3, 'พฤหัสบดี' => 4, 'ศุกร์' => 5, 'เสาร์' => 6];
        $targetDows = array_filter(array_map(fn($d) => $thaiDowMap[$d] ?? null, $daysOfWeek), fn($v) => $v !== null);
        if (empty($targetDows)) return [];

        $yearId = $semester->year_id;
        $holidays = Holiday::where('year_id', $yearId)->get();

        $dates = [];
        $cursor = $semester->start_date->copy();
        while ($cursor->lte($semester->end_date)) {
            if (in_array($cursor->dayOfWeek, $targetDows, true)) {
                $isHoliday = $holidays->contains(function ($h) use ($cursor) {
                    $end = $h->end_date ?? $h->start_date;
                    return $h->start_date && $cursor->between($h->start_date, $end);
                });
                if (!$isHoliday) $dates[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    // สร้างตาราง "บันทึกเวลาเรียน" แบบ เดือน > สัปดาห์ > วันที่ > คาบ
    // ต่างจาก buildClassDates() ตรงที่ยังคงวันหยุดไว้ในตาราง (ไม่ตัดออก) แต่ไม่นับคาบให้วันหยุด
    private function buildAttendanceGrid(TeachingAssign $assign, Semester $semester): array
    {
        if (!$semester->start_date || !$semester->end_date) return [];

        $daysOfWeek = TimetableSlot::where('assign_id', $assign->assign_id)
            ->pluck('day_of_week')->unique()->all();
        if (empty($daysOfWeek)) return [];

        $thaiDowMap = ['อาทิตย์' => 0, 'จันทร์' => 1, 'อังคาร' => 2, 'พุธ' => 3, 'พฤหัสบดี' => 4, 'ศุกร์' => 5, 'เสาร์' => 6];
        $targetDows = array_filter(array_map(fn($d) => $thaiDowMap[$d] ?? null, $daysOfWeek), fn($v) => $v !== null);
        if (empty($targetDows)) return [];

        $holidays = Holiday::where('year_id', $semester->year_id)->get();
        $thaiMonths = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',
                       7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];

        $months = [];
        $period = 0;
        $weekNum = 0;
        $lastIsoWeek = null;
        $currentMonthKey = null;

        $cursor = $semester->start_date->copy();
        while ($cursor->lte($semester->end_date)) {
            if (in_array($cursor->dayOfWeek, $targetDows, true)) {
                $isoWeek = $cursor->format('o-W');
                if ($isoWeek !== $lastIsoWeek) {
                    $weekNum++;
                    $lastIsoWeek = $isoWeek;
                    // ทั้งสัปดาห์ใช้ชื่อเดือนของวันแรกในสัปดาห์นั้น แม้บางวันจะข้ามไปเดือนถัดไปแล้ว
                    $currentMonthKey = $cursor->format('Y-m');
                    if (!isset($months[$currentMonthKey])) {
                        $months[$currentMonthKey] = ['label' => $thaiMonths[(int) $cursor->format('n')], 'weeks' => []];
                    }
                    $months[$currentMonthKey]['weeks'][$weekNum] = ['week' => $weekNum, 'dates' => []];
                }

                $isHoliday = $holidays->contains(function ($h) use ($cursor) {
                    $end = $h->end_date ?? $h->start_date;
                    return $h->start_date && $cursor->between($h->start_date, $end);
                });
                if (!$isHoliday) $period++;

                $months[$currentMonthKey]['weeks'][$weekNum]['dates'][] = [
                    'day' => $cursor->day,
                    'period' => $isHoliday ? null : $period,
                    'isHoliday' => $isHoliday,
                ];
            }
            $cursor->addDay();
        }

        return array_values($months);
    }
}
