<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\ScoreCategory;
use App\Models\Academic\StudentScore;
use App\Models\Academic\StudentSection;
use App\Models\Academic\ClassSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Semester;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    // เลือกวิชา-ห้องที่จะบันทึกคะแนน
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderBy('semester_id', 'desc')->get();

        $assigns = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'scoreCategories'])
            ->where('semester_id', $semesterId)
            ->orderBy('section_id')
            ->get();

        return view('academic.scores_index', compact('assigns', 'semesters', 'semesterId'));
    }

    // รายวิชาที่สอนในห้องเรียน
    public function sectionSubjects($sectionId)
    {
        $section = ClassSection::with(['level', 'semester.academicYear'])->findOrFail($sectionId);

        $assigns = TeachingAssign::with(['personnel', 'subject', 'scoreCategories'])
            ->where('section_id', $sectionId)
            ->orderBy('assign_id')
            ->get();

        $students = StudentSection::with('student')
            ->where('section_id', $sectionId)
            ->where('status', 'กำลังศึกษา')
            ->count();

        return view('academic.section_scores', compact('section', 'assigns', 'students'));
    }

    // หน้าบันทึกคะแนน (เลือก assign แล้ว)
    // เปิดหน้าบันทึกคะแนน
    public function manage($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'classSection.semester.academicYear', 'scoreCategories.studentScores'])
            ->findOrFail($assignId);

        // 1. จำกัดสิทธิ์: เช็คว่าคนที่ Login เป็นครูผู้สอนวิชานี้ หรือเป็น Admin หรือไม่
        $user = auth()->user();
        if ($user->role !== 'admin' && $user->personnel_id !== $assign->personnel_id) {
            return redirect()->route('scores.index')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหรือบันทึกคะแนนในรายวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น)');
        }

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        return view('academic.scores_manage', compact('assign', 'students', 'categories', 'scoreMatrix'));
    }

    // 2. ฟังก์ชันสำหรับพิมพ์ใบกรอกคะแนน (แบบในรูป)
    public function printScoreSheet($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'classSection.semester.academicYear', 'scoreCategories.studentScores', 'finalGrades'])
            ->findOrFail($assignId);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        return view('academic.scores_print', compact('assign', 'students', 'categories', 'scoreMatrix'));
    }

    // เพิ่มหมวดคะแนน
    public function storeCategory(Request $request)
    {
        $request->validate(['assign_id' => 'required', 'name' => 'required', 'max_score' => 'required|numeric']);
        ScoreCategory::create($request->only(['assign_id', 'name', 'max_score', 'weight_pct', 'sort_order', 'is_checkbox']));
        return redirect()->back()->with('success', 'เพิ่มหมวดคะแนนสำเร็จ');
    }

    public function updateCategory(Request $request, $id)
    {
        ScoreCategory::findOrFail($id)->update($request->only(['name', 'max_score', 'weight_pct', 'sort_order', 'is_checkbox']));
        return redirect()->back()->with('success', 'แก้ไขหมวดคะแนนสำเร็จ');
    }

    public function destroyCategory($id)
    {
        ScoreCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'ลบหมวดคะแนนสำเร็จ');
    }

    // บันทึกคะแนนทั้งหมด (submit ทีเดียว)
    public function saveScores(Request $request, $assignId)
    {
        $scores = $request->input('scores', []);
        // scores[student_id][category_id] = score_value
        foreach ($scores as $studentId => $categories) {
            foreach ($categories as $categoryId => $scoreValue) {
                StudentScore::updateOrCreate(
                    ['category_id' => $categoryId, 'student_id' => $studentId],
                    ['score' => $scoreValue !== '' ? $scoreValue : null]
                );
            }
        }
        return redirect()->back()->with('success', 'บันทึกคะแนนสำเร็จ');
    }

    // ตั้งค่าหมวดคะแนนมาตรฐาน
    public function setupCategories($assignId)
    {
        $assign = TeachingAssign::findOrFail($assignId);
        if ($assign->scoreCategories()->count() === 0) {
            $defaults = [
                ['name' => 'งานชิ้นที่ 1', 'max_score' => 10, 'weight_pct' => 10, 'sort_order' => 1, 'is_checkbox' => true],
                ['name' => 'งานชิ้นที่ 2', 'max_score' => 10, 'weight_pct' => 10, 'sort_order' => 2, 'is_checkbox' => true],
                ['name' => 'งานชิ้นที่ 3', 'max_score' => 10, 'weight_pct' => 10, 'sort_order' => 3, 'is_checkbox' => true],
                ['name' => 'กลางภาค', 'max_score' => 30, 'weight_pct' => 30, 'sort_order' => 4, 'is_checkbox' => false],
                ['name' => 'ปลายภาค', 'max_score' => 40, 'weight_pct' => 40, 'sort_order' => 5, 'is_checkbox' => false],
            ];
            foreach ($defaults as $d) {
                $assign->scoreCategories()->create($d);
            }
        }
        return redirect()->back()->with('success', 'ตั้งค่าสัดส่วนคะแนนมาตรฐานแล้ว');
    }

    // คำนวณเกรดจากคะแนน
    public function calculateGrades(Request $request, $assignId)
    {
        $assign = TeachingAssign::with('scoreCategories.studentScores')->findOrFail($assignId);
        $categories = $assign->scoreCategories;

        // ดึง student_ids ทั้งหมดที่มีคะแนน
        $studentIds = StudentSection::where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->pluck('student_id');

        foreach ($studentIds as $studentId) {
            $totalWeighted = 0;
            $totalWeight = 0;

            foreach ($categories as $cat) {
                $score = $cat->studentScores->where('student_id', $studentId)->first();
                if ($score && $score->score !== null && $cat->max_score > 0) {
                    $pct = ($score->score / $cat->max_score) * 100;
                    $totalWeighted += $pct * ($cat->weight_pct / 100);
                    $totalWeight += $cat->weight_pct;
                }
            }

            $finalScore = $totalWeight > 0 ? ($totalWeighted / $totalWeight) * 100 : 0;
            $gradeInfo = FinalGrade::calculateGrade($finalScore);

            FinalGrade::updateOrCreate(
                ['student_id' => $studentId, 'assign_id' => $assignId, 'semester_id' => $assign->semester_id],
                [
                    'total_score' => round($finalScore, 2),
                    'grade' => $gradeInfo['grade'],
                    'gpa_point' => $gradeInfo['gpa'],
                    'remark' => $finalScore >= 50 ? 'ผ่าน' : 'ไม่ผ่าน',
                ]
            );
        }

        return redirect()->back()->with('success', 'คำนวณเกรดสำเร็จ');
    }
}