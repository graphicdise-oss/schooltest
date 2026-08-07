<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Subject;
use App\Models\Academic\TeachingAssign;
use Illuminate\Http\Request;

class AcademicReportController extends Controller
{
    // ===== รายงานคะแนนเฉลี่ย 2 ภาคเรียน (Excel) =====

    // หน้าเลือกปี/ระดับ/ห้อง — เลือกห้องของภาคเรียนที่ 1 แล้วระบบไปหาภาคเรียนที่ 2 ของห้องเดียวกันเอง (ดู avgScoreExport)
    public function avgScoreForm(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $yearId = $request->year_id ?? (AcademicYear::where('is_current', true)->value('year_id') ?? $academicYears->first()?->year_id);

        $sem1 = Semester::where('year_id', $yearId)->where('semester_name', '1')->first();

        $levelId = $request->level_id;
        $levels = $sem1
            ? Level::whereHas('classSections', fn ($q) => $q->where('semester_id', $sem1->semester_id))
                ->orderBy('sort_order')->get()
            : collect();

        $sections = $sem1
            ? ClassSection::with('level')->where('semester_id', $sem1->semester_id)
                ->when($levelId, fn ($q) => $q->where('level_id', $levelId))
                ->orderBy('level_id')->orderBy('section_number')->get()
            : collect();

        $sectionId = $request->section_id;

        // พรีวิวรายชื่อนักเรียนของห้องที่เลือก ให้เห็นก่อนกดส่งออกจริง
        $roster = $sectionId
            ? StudentSection::with('student')
                ->where('section_id', $sectionId)
                ->where('status', 'กำลังศึกษา')
                ->orderBy('student_number')->get()
            : collect();

        return view('academic.report_avg_score', compact(
            'academicYears', 'yearId', 'levels', 'levelId', 'sections', 'sectionId', 'sem1', 'roster'
        ));
    }

    public function avgScoreExport(Request $request)
    {
        $request->validate(['section_id' => 'required|exists:class_sections,section_id']);

        $section1 = ClassSection::with(['level', 'semester.academicYear'])->findOrFail($request->section_id);
        $year = $section1->semester->academicYear;
        $sem1 = $section1->semester;
        $sem2 = Semester::where('year_id', $year->year_id)->where('semester_name', '2')->first();

        $roster = StudentSection::with('student')
            ->where('section_id', $section1->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')->get();

        $semesterIds = array_values(array_filter([$sem1->semester_id, $sem2->semester_id ?? null]));

        $grades = FinalGrade::with('teachingAssign.subject')
            ->whereIn('student_id', $roster->pluck('student_id'))
            ->whereIn('semester_id', $semesterIds)
            ->whereHas('teachingAssign.subject', fn ($q) => $q->whereIn('subject_type', ['พื้นฐาน', 'เพิ่มเติม']))
            ->get();

        // รวมรายวิชาที่เจอทั้งหมด (จากทั้ง 2 ภาคเรียน) แยกกลุ่มพื้นฐาน/เพิ่มเติม แต่ละวิชาคือ 1 กลุ่มคอลัมน์
        // (วิชาที่มีแค่ภาคเรียนเดียว เช่น "ภาษาไทย 5" สอนแค่เทอม 1 ก็ยังขึ้นเป็นกลุ่มคอลัมน์ของตัวเอง อีกภาคเรียนว่างไว้)
        $subjectsSeen = [];
        foreach ($grades as $g) {
            $subj = $g->teachingAssign->subject ?? null;
            if ($subj && !isset($subjectsSeen[$subj->subject_id])) {
                $subjectsSeen[$subj->subject_id] = $subj;
            }
        }
        $basicSubjects = collect($subjectsSeen)->filter(fn ($s) => $s->subject_type === 'พื้นฐาน')->values();
        $extraSubjects = collect($subjectsSeen)->filter(fn ($s) => $s->subject_type === 'เพิ่มเติม')->values();
        $allSubjects = $basicSubjects->concat($extraSubjects)->values();

        $gradeMap = [];
        foreach ($grades as $g) {
            $subj = $g->teachingAssign->subject ?? null;
            if ($subj) {
                $gradeMap[$g->student_id][$subj->subject_id][$g->semester_id] = $g;
            }
        }

        $rows = [];
        foreach ($roster as $ss) {
            $student = $ss->student;
            if (!$student) continue;

            $subjectCols = [];
            $totalScore = 0;
            $gpaPoints = 0;
            $gpaCredits = 0;

            foreach ($allSubjects as $subj) {
                $g1 = $gradeMap[$student->student_id][$subj->subject_id][$sem1->semester_id] ?? null;
                $g2 = $sem2 ? ($gradeMap[$student->student_id][$subj->subject_id][$sem2->semester_id] ?? null) : null;
                $s1 = $g1?->total_score;
                $s2 = $g2?->total_score;
                $present = collect([$s1, $s2])->filter(fn ($v) => $v !== null);
                $avg = $present->count() ? round($present->sum() / $present->count(), 2) : null;
                $point = $avg !== null ? FinalGrade::calculateGrade($avg)['gpa'] : null;

                $subjectCols[] = ['s1' => $s1, 's2' => $s2, 'sum' => $present->count() ? $present->sum() : null, 'avg' => $avg, 'point' => $point];

                if ($avg !== null) {
                    $totalScore += $present->sum();
                    $credit = (float) ($subj->credits ?? 0);
                    $gpaPoints += $point * $credit;
                    $gpaCredits += $credit;
                }
            }

            $rows[] = [
                'student' => $student,
                'subjectCols' => $subjectCols,
                'total' => $totalScore,
                'avg' => $allSubjects->count() ? round($totalScore / $allSubjects->count(), 2) : 0,
                'gpa' => $gpaCredits > 0 ? round($gpaPoints / $gpaCredits, 2) : 0,
            ];
        }

        // จัดอันดับตามคะแนนรวม มากไปน้อย แบบ competition ranking (คะแนนเท่ากันได้อันดับเดียวกัน อันดับถัดไปข้ามตามจำนวนคนที่เสมอ)
        $rankByStudentId = [];
        $rank = 0;
        $prevTotal = null;
        $seen = 0;
        foreach (collect($rows)->sortByDesc('total')->values() as $r) {
            $seen++;
            if ($r['total'] !== $prevTotal) {
                $rank = $seen;
                $prevTotal = $r['total'];
            }
            $rankByStudentId[$r['student']->student_id] = $rank;
        }
        foreach ($rows as &$r) {
            $r['rank'] = $rankByStudentId[$r['student']->student_id];
        }
        unset($r);

        $maxScore = $allSubjects->count() * 200;

        $filename = 'รายงานคะแนนเฉลี่ย2ภาคเรียน_' . ($section1->level->name ?? '') . '-' . $section1->section_number . '_' . $year->year_name . '.xls';

        $html = view('academic.report_avg_score_excel', compact(
            'section1', 'year', 'sem1', 'sem2', 'basicSubjects', 'extraSubjects', 'allSubjects', 'rows', 'maxScore'
        ))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . rawurlencode($filename) . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    // ===== รายงานจัดอันดับคะแนนรายวิชา (พิมพ์/PDF) =====

    public function subjectRankForm(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $yearId = $request->year_id ?? (AcademicYear::where('is_current', true)->value('year_id') ?? $academicYears->first()?->year_id);
        $term = $request->term ?? (Semester::where('is_current', true)->value('semester_name') ?? '1');

        $semester = Semester::where('year_id', $yearId)->where('semester_name', $term)->first();

        $subjects = collect();
        if ($semester) {
            $subjectIds = TeachingAssign::where('semester_id', $semester->semester_id)->pluck('subject_id')->unique();
            $subjects = Subject::whereIn('subject_id', $subjectIds)
                ->whereIn('subject_type', ['พื้นฐาน', 'เพิ่มเติม'])
                ->orderBy('name_th')->get();
        }

        $subjectId = $request->subject_id;

        return view('academic.report_subject_rank', compact(
            'academicYears', 'yearId', 'term', 'subjects', 'subjectId'
        ));
    }

    public function subjectRankPrint(Request $request)
    {
        $request->validate([
            'year_id' => 'required|exists:academic_years,year_id',
            'term' => 'required',
            'subject_id' => 'required|exists:subjects,subject_id',
        ]);

        $year = AcademicYear::findOrFail($request->year_id);
        $semester = Semester::where('year_id', $year->year_id)->where('semester_name', $request->term)->firstOrFail();
        $subject = Subject::findOrFail($request->subject_id);

        $grades = FinalGrade::with(['student', 'teachingAssign.classSection.level'])
            ->where('semester_id', $semester->semester_id)
            ->whereHas('teachingAssign', fn ($q) => $q->where('subject_id', $subject->subject_id))
            ->get()
            ->filter(fn ($g) => $g->student !== null)
            ->sortByDesc(fn ($g) => (float) $g->total_score)
            ->values();

        // จัดอันดับแบบ competition ranking เช่นเดียวกับรายงานคะแนนเฉลี่ย 2 ภาคเรียน
        $rows = [];
        $rank = 0;
        $prevScore = null;
        $seen = 0;
        foreach ($grades as $g) {
            $seen++;
            $score = (float) $g->total_score;
            if ($score !== $prevScore) {
                $rank = $seen;
                $prevScore = $score;
            }
            $rows[] = ['rank' => $rank, 'grade' => $g];
        }

        $school = config('school');

        return view('academic.report_subject_rank_print', compact('year', 'semester', 'subject', 'rows', 'school'));
    }
}
