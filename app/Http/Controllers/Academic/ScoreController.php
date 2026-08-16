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
use App\Models\Academic\Subject;
use App\Models\Personne\Personnel;
use App\Services\ExcelSchoolHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScoreController extends Controller
{
    private const FIRST_SCORE_COL = 5;

    // เลือกวิชา-ห้องที่จะบันทึกคะแนน
    public function index(Request $request)
    {
        $semesterId  = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $subjectId   = $request->subject_id;
        $personnelId = $request->personnel_id;

        $semesters  = Semester::with('academicYear')->orderedByRecency()->get();
        $subjects   = Subject::where('is_active', true)->orderBy('code')->get();
        $teachers   = Personnel::where('status', 'ปฏิบัติงาน')->orderBy('thai_firstname')->get();

        $query = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'scoreCategories'])
            ->where('semester_id', $semesterId)
            ->orderBy('section_id');

        if ($subjectId)   $query->where('subject_id', $subjectId);
        if ($personnelId) $query->where('personnel_id', $personnelId);

        $assigns = $query->get();

        return view('academic.scores_index', compact('assigns', 'semesters', 'subjects', 'teachers', 'semesterId', 'subjectId', 'personnelId'));
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
        if (!$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
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

    // ดาวน์โหลดแบบฟอร์ม Excel เปล่า (จริงๆ คือ pre-fill คะแนนปัจจุบันไว้ให้แก้) สำหรับนำเข้าคะแนนของ assign นี้
    public function importTemplate($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level', 'classSection.semester.academicYear'])
            ->findOrFail($assignId);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $categories = $assign->scoreCategories()->with('studentScores')->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('คะแนน');

        $totalCols = self::FIRST_SCORE_COL - 1 + $categories->count();
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $subjectLabel = "{$assign->subject->code} {$assign->subject->name_th} — "
            . ($assign->classSection->level->name ?? '') . '/' . $assign->classSection->section_number;

        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols, null);
        ExcelSchoolHeader::applyInstructionRow(
            $sheet, $totalCols,
            "แบบฟอร์มนำเข้าคะแนน วิชา {$subjectLabel} — กรอก/แก้ไขคะแนนเริ่มจากแถวที่ 7 เป็นต้นไป (แถวที่ 1-6 ห้ามลบ/ห้ามแก้), เว้นว่างช่องไหนไว้ = ไม่แก้คะแนนเดิมของช่องนั้น"
        );

        $headers = ['ลำดับ', 'เลขที่', 'รหัสนักเรียน', 'ชื่อ-นามสกุล'];
        foreach ($categories as $cat) {
            $headers[] = "{$cat->name} (เต็ม {$cat->max_score})";
        }

        foreach ($headers as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            if ($i !== 0 || !$hasLogo) {
                $sheet->getColumnDimension($col)->setWidth($i === 3 ? 26 : 16);
            }
            $sheet->setCellValue("{$col}6", $header);
        }
        $sheet->getStyle("A6:{$lastCol}6")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(28);

        $row = 7;
        foreach ($students as $i => $ss) {
            $s = $ss->student;
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $ss->student_number);
            $sheet->setCellValue("C{$row}", $s->student_code);
            $sheet->setCellValue("D{$row}", trim(($s->thai_prefix ?? '') . $s->thai_firstname . ' ' . $s->thai_lastname));
            foreach ($categories as $ci => $cat) {
                $col = Coordinate::stringFromColumnIndex(self::FIRST_SCORE_COL + $ci);
                $val = $scoreMatrix[$s->student_id][$cat->category_id] ?? null;
                if ($val !== null) {
                    $sheet->setCellValue("{$col}{$row}", $val);
                }
            }
            $row++;
        }
        $sheet->getStyle("A7:{$lastCol}" . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->freezePane('A7');

        $tmpPath = tempnam(sys_get_temp_dir(), 'scores_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'แบบฟอร์มนำเข้าคะแนน_' . $assign->subject->code . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // รับไฟล์ Excel ที่กรอกคะแนนมา แล้วรันคำสั่งนำเข้าให้
    public function importUpload(Request $request, $assignId)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel',
            'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
        ]);

        set_time_limit(0);

        $path = $request->file('file')->store('imports');
        $fullPath = Storage::path($path);

        $options = ['assignId' => $assignId, 'file' => $fullPath];
        if ($request->boolean('dry_run')) {
            $options['--dry-run'] = true;
        }

        Artisan::call('import:scores', $options);
        $output = Artisan::output();

        @unlink($fullPath);

        return back()->with('import_output', $output);
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

        $school = \App\Models\Academic\Pp2Setting::mergedSchoolConfig();

        return view('academic.scores_print', compact('assign', 'students', 'categories', 'scoreMatrix', 'school'));
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

            // หาร 100 เสมอ (ไม่ใช่ totalWeight) เพราะหมวดที่ไม่มีคะแนนนับเป็น 0
            $finalScore = round($totalWeighted, 2);
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