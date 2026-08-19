<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Level;
use App\Models\Academic\StudentSection;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\TeachingAssign;
use App\Models\Student;
use App\Services\ExcelSchoolHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GradeController extends Controller
{
    // หน้ารวมผลการเรียน (เลือกเทอม + ห้อง)
    public function index(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderedByRecency()->get();
        $levels  = Level::orderBy('sort_order')->get();
        $levelId = $request->level_id;
        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')->orderBy('section_number')->get();

        return view('academic.grades_index', compact('semesters', 'sections', 'semesterId', 'levels', 'levelId'));
    }

    // ผลการเรียนรายคน (Transcript)
    public function studentTranscript($studentId)
    {
        $student = Student::findOrFail($studentId);
        [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);
        return view('academic.transcript', compact('student', 'grades', 'gpa', 'totalCredits'));
    }

    // พิมพ์ใบ Transcript รายคน
  public function printTranscript(Request $request, $studentId)
{
    $student = Student::findOrFail($studentId);
    [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);

    // ==========================================
    // จัดการข้อมูลตามเงื่อนไขที่ส่งมาจาก Modal
    // ==========================================

    // 1. กรองเฉพาะเทอมที่ติ๊กเลือกเอาไว้
    if ($request->has('selected_semesters')) {
        $selectedKeys = $request->input('selected_semesters');
        $grades = $grades->filter(function($value, $key) use ($selectedKeys) {
            return in_array($key, $selectedKeys);
        });
    }

    // 2. ถ้าติ๊ก "ไม่คำนวณ/ไม่แสดงเกรดภาคเรียนสุดท้าย" ให้ลบข้อมูลเทอมล่าสุดทิ้ง
    if ($request->boolean('hide_last_semester') && $grades->count() > 0) {
        $lastKey = $grades->keys()->last();
        $grades->forget($lastKey);
    }

    // 3. คำนวณ GPA และหน่วยกิตสะสมใหม่ (หลังจากการกรองเทอมด้านบนแล้ว)
    $totalCredits = 0;
    $totalPoints  = 0;
    foreach ($grades as $semGrades) {
        foreach ($semGrades as $g) {
            $credits = $g->teachingAssign->subject->credits ?? 0;
            $totalCredits += $credits;
            $totalPoints  += ($g->gpa_point ?? 0) * $credits;
        }
    }
    $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

    // 4. รวบรวม option อื่นๆ เพื่อส่งไปจัดการซ่อน/แสดง ในหน้ากระดาษ (Blade)
    $options = [
        'show_original' => $request->boolean('show_original'),
        'hide_profile'  => $request->boolean('hide_profile'),
        'english_report'=> $request->boolean('english_report'),
    ];

    return view('academic.transcript_print', compact('student', 'grades', 'gpa', 'totalCredits', 'options'));
}

    // หน้าแก้ไขเกรดรายคน
    public function editStudentGrades($studentId)
    {
        $student = Student::findOrFail($studentId);
        [$grades, $gpa, $totalCredits] = $this->buildTranscriptData($studentId);
        return view('academic.grades_edit', compact('student', 'grades', 'gpa', 'totalCredits'));
    }

    // ดาวน์โหลดแบบฟอร์มนำเข้าเกรดรวม (Transcript) เปล่า — สูงสุด 3 ปีการศึกษา/ระดับชั้น เคียงกัน สำหรับนักเรียนคนนี้คนเดียว
    public function importTranscriptTemplate($studentId)
    {
        $student = Student::findOrFail($studentId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('เกรด');
        $this->buildTranscriptSheet($sheet, $student);

        $tmpPath = tempnam(sys_get_temp_dir(), 'transcript_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'แบบฟอร์มนำเข้าเกรดรวม_' . ($student->student_code ?: $student->student_id) . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * เขียนแบบฟอร์มนำเข้าเกรดรวม (Transcript) ของนักเรียน 1 คนลงชีตที่ให้มา — ใช้ร่วมกันทั้ง
     * importTranscriptTemplate() (ไฟล์ 1 ชีตต่อ 1 คน) และ importBulkTemplate() (ไฟล์เดียวหลายชีต
     * เรียกเมธอดนี้วนทีละคน) ให้หน้าตา/โครงสร้างเหมือนกันเป๊ะๆ ไม่ต้องแก้ 2 ที่เวลาปรับฟอร์ม
     *
     * มีแถว "ชื่อ-สกุล"/"รหัสนักศึกษา" บอกไว้ชัดๆ ว่าชีตนี้เป็นของใคร — import:transcript-bulk ใช้แถว
     * "รหัสนักศึกษา" นี้เองหาว่าแต่ละชีตในไฟล์ทั้งห้องเป็นของนักเรียนคนไหน (ดู ParsesWideTranscriptSheet)
     */
    private function buildTranscriptSheet($sheet, Student $student): void
    {
        $totalCols = 9; // 3 กลุ่มปี x 3 คอลัมน์

        $hasLogo = ExcelSchoolHeader::apply($sheet, $totalCols, null);
        ExcelSchoolHeader::applyInstructionRow(
            $sheet, $totalCols,
            "แบบฟอร์มนำเข้าเกรดรวม (Transcript) ของ {$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} — "
            . 'กรอกได้สูงสุด 3 ปีการศึกษา/ระดับชั้น เคียงกัน (กรอกไม่ครบ 3 กลุ่มก็ได้ และแต่ละคนมีจำนวนวิชาไม่เท่ากันได้ ไม่ต้องกรอกให้ครบทุกแถว), '
            . 'แถว "ปีการศึกษา" ให้ใส่ปี พ.ศ. เช่น 2567 และแถว "ระดับชั้น" ให้ใส่แบบย่อ เช่น ม.4, ป.6, อ.2, '
            . 'แถวคั่นภาคเรียนใส่ "ภาคเรียนที่ 1" หรือ "ภาคเรียนที่ 2", แถววิชาใส่ "รหัสวิชา : ชื่อวิชา" หน่วยกิต เกรด(0-4) ตามลำดับ, '
            . 'ด้านล่างสุดเป็นตารางกิจกรรมพัฒนาผู้เรียนแยกต่างหาก'
        );

        for ($g = 0; $g < 3; $g++) {
            $col = 1 + $g * 3;
            $colLetter = Coordinate::stringFromColumnIndex($col);
            ExcelSchoolHeader::setColumnWidth($sheet, $colLetter, 26, $hasLogo);
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col + 1))->setWidth(8);
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col + 2))->setWidth(8);
        }

        // แถวชื่อ-สกุล / รหัสนักศึกษา — บอกไว้ชัดๆ ว่าแผ่นนี้ของใคร (จำเป็นมากตอนไฟล์ทั้งห้องมีหลายชีต)
        $studentName = trim("{$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname}");
        $identityRow1 = 6;
        $identityRow2 = 7;
        $this->writeIdentityRow($sheet, $totalCols, $identityRow1, 'ชื่อ-สกุล', $studentName);
        $this->writeIdentityRow($sheet, $totalCols, $identityRow2, 'รหัสนักศึกษา', (string) ($student->student_code ?? ''));

        for ($g = 0; $g < 3; $g++) {
            $col = 1 + $g * 3;
            $this->writeTranscriptGroupHeader($sheet, $col, $identityRow2 + 1, $identityRow2 + 2, $identityRow2 + 4, ['รหัส/รายวิชา', 'หน่วยกิต', 'ผลการเรียน']);
        }
        // เผื่อแถวว่างต่อภาคเรียนไว้เยอะๆ (30 แถว) กันกรณีเทอมนั้นมีวิชาเยอะ (เช่น มีวิชาเพิ่มเติมหลายตัว) ล้นไปทับ
        // แถวคั่นภาคเรียนถัดไปพอดี — ของจริงเจอเคสเทอมเดียวมีถึง 19 วิชา เผื่อ 17 แถวเดิมไม่พอ
        $subjectRowsPerSemester = 30;
        $sem1From = $identityRow2 + 5;
        $sem1To   = $sem1From + $subjectRowsPerSemester - 1;
        $sem2MarkerRow = $sem1To + 1;
        $sem2From = $sem2MarkerRow + 1;
        $sem2To   = $sem2From + $subjectRowsPerSemester - 1;

        $this->writeTranscriptGroupBlanks($sheet, $totalCols, $sem1From, $sem1To);
        for ($g = 0; $g < 3; $g++) {
            $col = 1 + $g * 3;
            $this->writeTranscriptSemesterMarker($sheet, $col, $sem2MarkerRow, 'ภาคเรียนที่ 2');
        }
        $this->writeTranscriptGroupBlanks($sheet, $totalCols, $sem2From, $sem2To);

        // ตารางกิจกรรมพัฒนาผู้เรียน (แนะแนว/ชุมนุม/กิจกรรมเพื่อสังคมฯ) — แยกจากตารางผลการเรียนข้างบน
        $actInstructionRow = $sem2To + 2;
        $sheet->mergeCells("A{$actInstructionRow}:" . Coordinate::stringFromColumnIndex($totalCols) . $actInstructionRow);
        $sheet->setCellValue("A{$actInstructionRow}", 'ตารางกิจกรรมพัฒนาผู้เรียน (แยกจากตารางผลการเรียนด้านบน) — ผลการประเมินให้ใส่ "ผ" (ผ่าน) หรือ "มผ" (ไม่ผ่าน)');
        $sheet->getStyle("A{$actInstructionRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'B8720A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF4E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        $activityRows = ['แนะแนว' => 20, 'ชุมนุม' => 20, 'กิจกรรมเพื่อสังคมและสาธารณประโยชน์' => 10];
        $actRowsPerSemester = 6; // เผื่อแถวว่างเกินจำนวนกิจกรรมเริ่มต้น (3 อย่าง) ไว้เผื่อโรงเรียนมีกิจกรรมเพิ่มเติม
        $actLabelRow = $actInstructionRow + 1;
        $actYearRow  = $actLabelRow + 1;
        $actSemRow   = $actYearRow + 2;
        $actSem1From = $actSemRow + 1;
        $actSem1To   = $actSem1From + $actRowsPerSemester - 1;
        $actSem2MarkerRow = $actSem1To + 1;
        $actSem2From = $actSem2MarkerRow + 1;
        $actSem2To   = $actSem2From + $actRowsPerSemester - 1;

        for ($g = 0; $g < 3; $g++) {
            $col = 1 + $g * 3;
            $this->writeTranscriptGroupHeader($sheet, $col, $actLabelRow, $actYearRow, $actSemRow, ['กิจกรรม', 'เวลา (ชั่วโมง)', 'ผลการประเมิน']);

            $row = $actSem1From;
            foreach ($activityRows as $actName => $hours) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$row}", $actName);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . $row, $hours);
                $row++;
            }
            $this->writeTranscriptSemesterMarker($sheet, $col, $actSem2MarkerRow, 'ภาคเรียนที่ 2');
            $row = $actSem2From;
            foreach ($activityRows as $actName => $hours) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$row}", $actName);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1) . $row, $hours);
                $row++;
            }
        }
        $this->writeTranscriptGroupBlanks($sheet, $totalCols, $actSem1From, $actSem2To);

        $sheet->freezePane('A' . $sem1From);
    }

    // แถว "ชื่อ-สกุล" หรือ "รหัสนักศึกษา" ของแบบฟอร์มนำเข้าเกรดรวม — เต็มความกว้างตาราง (ไม่ใช่แค่กลุ่มเดียว
    // เหมือนแถวปีการศึกษา/ระดับชั้น เพราะเป็นค่าเดียวของทั้งชีต) ป้ายชื่ออยู่คอลัมน์แรก ที่เหลือ merge เป็นช่องค่า
    private function writeIdentityRow($sheet, int $totalCols, int $row, string $label, string $value): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);

        $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("B{$row}", $value);
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
    }

    // เขียนหัวกลุ่ม 1 กลุ่ม (3 คอลัมน์ติดกัน) ของแบบฟอร์มนำเข้าเกรดรวม:
    // แถวป้ายคอลัมน์ + แถวปีการศึกษา + แถวระดับชั้น (คนละแถว คนละช่องกรอก) + แถวภาคเรียนที่ 1
    // semRow ต้องเท่ากับ yearRow + 2 เสมอ (เว้นแถวระดับชั้นไว้ตรงกลาง)
    private function writeTranscriptGroupHeader($sheet, int $col, int $labelRow, int $yearRow, int $semRow, array $labels): void
    {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $lastColLetter = Coordinate::stringFromColumnIndex($col + 2);

        foreach ($labels as $i => $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + $i) . $labelRow, $label);
        }
        $sheet->getStyle("{$colLetter}{$labelRow}:{$lastColLetter}{$labelRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $this->writeTranscriptYearLevelRow($sheet, $col, $yearRow, 'ปีการศึกษา');
        $this->writeTranscriptYearLevelRow($sheet, $col, $yearRow + 1, 'ระดับชั้น');

        $this->writeTranscriptSemesterMarker($sheet, $col, $semRow, 'ภาคเรียนที่ 1');
    }

    // แถว "ปีการศึกษา" หรือ "ระดับชั้น" ของแบบฟอร์มนำเข้าเกรดรวม — ป้ายชื่ออยู่คอลัมน์แรก
    // ส่วนอีก 2 คอลัมน์ที่เหลือ merge เป็นช่องกรอกว่างๆ แยกต่างหาก (ไม่มีขีดใต้ให้พิมพ์ทับ)
    private function writeTranscriptYearLevelRow($sheet, int $col, int $row, string $label): void
    {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $inputColLetter = Coordinate::stringFromColumnIndex($col + 1);
        $inputLastColLetter = Coordinate::stringFromColumnIndex($col + 2);

        $sheet->setCellValue("{$colLetter}{$row}", $label);
        $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF2F8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);

        $sheet->mergeCells("{$inputColLetter}{$row}:{$inputLastColLetter}{$row}");
        $sheet->getStyle("{$inputColLetter}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B8C1']]],
        ]);
    }

    // แถวคั่นภาคเรียน (merge 3 คอลัมน์ในกลุ่มเดียวกัน) ของแบบฟอร์มนำเข้าเกรดรวม
    private function writeTranscriptSemesterMarker($sheet, int $col, int $row, string $text): void
    {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $lastColLetter = Coordinate::stringFromColumnIndex($col + 2);

        $sheet->mergeCells("{$colLetter}{$row}:{$lastColLetter}{$row}");
        $sheet->setCellValue("{$colLetter}{$row}", $text);
        $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '37474F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    // ใส่เส้นขอบบางๆ ให้ช่องกรอกข้อมูลว่างของแบบฟอร์มนำเข้าเกรดรวม
    private function writeTranscriptGroupBlanks($sheet, int $totalCols, int $fromRow, int $toRow): void
    {
        $sheet->getStyle('A' . $fromRow . ':' . Coordinate::stringFromColumnIndex($totalCols) . $toRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'EEEEEE']]],
        ]);
    }

    // รับไฟล์ Excel เกรดรวมที่กรอกมา แล้วรันคำสั่งนำเข้าให้นักเรียนคนนี้
    // ครอบทั้งฟังก์ชันด้วย try/catch เพราะการอัปโหลดไฟล์มีจุดพังได้หลายจุด (validate() เองก็แตะไฟล์
    // เพื่อเดา mime type, store() ก็แตะไฟล์อีกที) — ถ้าไฟล์ชั่วคราวหายไปกลางทาง อยากให้ขึ้นข้อความแจ้งเตือน
    // สวยๆ ไม่ใช่หน้า error ระบบ ยกเว้น validation error ปกติที่ต้องปล่อยผ่านให้ Laravel จัดการแบบเดิม
    public function importTranscriptUpload(Request $request, $studentId)
    {
        Student::findOrFail($studentId);

        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx',
            ], [
                'file.required' => 'กรุณาเลือกไฟล์ Excel',
                'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
            ]);

            set_time_limit(0);

            $uploaded = $request->file('file');
            if (! $uploaded || ! $uploaded->isValid()) {
                return back()->with('error', 'อัปโหลดไฟล์ไม่สำเร็จ (ไฟล์อาจเสียหายหรือใหญ่เกินไป) กรุณาเลือกไฟล์แล้วลองใหม่อีกครั้ง');
            }

            $fullPath = $this->moveUploadedFileToTemp($uploaded);
            if (! $fullPath) {
                return back()->with('error', 'ระบบเข้าถึงไฟล์ที่อัปโหลดไม่ได้ (มักเกิดจากโปรแกรมป้องกันไวรัสสแกนไฟล์ค้างอยู่ พบบ่อยกับไฟล์ขนาดใหญ่บน Windows) กรุณาลองใหม่อีกครั้ง หรือปิด real-time protection ชั่วคราวแล้วลองใหม่');
            }

            $options = ['studentId' => $studentId, 'file' => $fullPath];
            if ($request->boolean('dry_run')) {
                $options['--dry-run'] = true;
            }

            Artisan::call('import:transcript', $options);
            $output = Artisan::output();

            @unlink($fullPath);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'นำเข้าไฟล์ไม่สำเร็จ: ' . $e->getMessage());
        }

        return back()->with('transcript_import_output', $output);
    }

    // ย้ายไฟล์ temp ที่เพิ่งอัปโหลดมาไปเก็บที่ path ใหม่ที่เราคุมเอง โดยใช้ move_uploaded_file() ของ PHP
    // ตรงๆ (ไม่ผ่าน Symfony/Laravel UploadedFile::store()/getRealPath()+fopen() ที่ล้มเหลวบนบางเครื่อง
    // Windows แม้จะลองรออ่านซ้ำแล้วก็ตาม) — move_uploaded_file() เป็นฟังก์ชันของ PHP เองที่ออกแบบมา
    // สำหรับไฟล์ที่อัปโหลดผ่าน HTTP โดยเฉพาะ ใช้กลไกภายในของ PHP เอง (is_uploaded_file) ไม่ใช่การเปิด
    // ไฟล์ตรงๆ แบบ fopen ทั่วไป มีโอกาสรอดจากปัญหาไฟล์ถูกล็อกได้มากกว่า — ลองซ้ำสั้นๆ เผื่อไฟล์ยังไม่พร้อม
    private function moveUploadedFileToTemp(\Illuminate\Http\UploadedFile $uploaded, int $maxAttempts = 6, int $delayMs = 200): ?string
    {
        $source = $uploaded->getPathname();
        if (! $source) {
            return null;
        }

        $dest = tempnam(sys_get_temp_dir(), 'grade_import_') . '.xlsx';

        for ($i = 0; $i < $maxAttempts; $i++) {
            if (is_uploaded_file($source) && @move_uploaded_file($source, $dest)) {
                return $dest;
            }
            // เผื่อ move_uploaded_file ใช้ไม่ได้เพราะบริบทนี้ไม่ใช่ HTTP request จริง (เช่น artisan tinker/test) —
            // ลอง copy() ธรรมดาสำรองไว้ด้วย
            if (is_file($source) && @copy($source, $dest)) {
                return $dest;
            }
            usleep($delayMs * 1000);
        }

        @unlink($dest);
        return null;
    }

    // หน้ารวม "นำเข้าเกรดรวมทั้งห้อง" — เลือกปี/เทอม/ระดับ/ห้อง แล้วโหลดฟอร์ม/อัปโหลดไฟล์รวมของทั้งห้องได้
    public function importBulkIndex(Request $request)
    {
        $academicYears = AcademicYear::orderBy('year_name', 'desc')->get();

        $currentSem = Semester::where('is_current', true)->with('academicYear')->first();
        $yearId    = $request->year_id ?? ($currentSem->year_id ?? $academicYears->first()?->year_id);
        $term      = $request->term ?? ($currentSem->semester_name ?? '1');
        $levelId   = $request->level_id;
        $sectionId = $request->section_id;

        $semester = Semester::where('year_id', $yearId)->where('semester_name', $term)->first();
        $semesterId = $semester?->semester_id;

        $levels = Level::whereHas('classSections', fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy('sort_order')->get();

        $sections = ClassSection::with('level')
            ->where('semester_id', $semesterId)
            ->when($levelId, fn($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')->orderBy('section_number')->get();

        $students = collect();
        $currentSection = null;
        if ($sectionId) {
            $currentSection = ClassSection::with('level')->find($sectionId);
            $students = StudentSection::with('student')
                ->where('section_id', $sectionId)
                ->where('status', 'กำลังศึกษา')
                ->orderBy('student_number')->get();
        }

        return view('academic.grades_import_bulk', compact(
            'academicYears', 'yearId', 'term', 'levelId', 'sectionId', 'levels', 'sections', 'students', 'currentSection'
        ));
    }

    // ดาวน์โหลดแบบฟอร์มนำเข้าเกรดรวมทั้งห้อง (Bulk) — เวิร์กบุ๊กเดียว หลายชีต ชีตละ 1 คนในห้องนั้น
    // (ชีตที่ 1 เป็นสารบัญ) แต่ละชีตหน้าตาเหมือน importTranscriptTemplate() ทุกประการเพราะเรียก
    // buildTranscriptSheet() ตัวเดียวกัน แค่มีแถว "รหัสนักศึกษา" ให้ import:transcript-bulk ใช้แยกว่า
    // ชีตไหนเป็นของใครตอนอัปโหลดกลับเข้ามา
    public function importBulkTemplate($sectionId)
    {
        $section = ClassSection::with('level')->findOrFail($sectionId);
        $roster = StudentSection::with('student')
            ->where('section_id', $sectionId)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')->get();

        $spreadsheet = new Spreadsheet();

        // ชีตแรก: สารบัญ/รายชื่อนักเรียนในห้องนี้ (parser ข้ามชีตนี้ไปเองเพราะไม่มีแถว "รหัสนักศึกษา"/"ปีการศึกษา")
        $indexSheet = $spreadsheet->getActiveSheet();
        $indexSheet->setTitle('รายชื่อ');
        $indexSheet->fromArray(['เลขที่', 'รหัสนักศึกษา', 'ชื่อ-สกุล'], null, 'A1');
        $indexSheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '37474F']],
        ]);
        $indexSheet->getColumnDimension('A')->setWidth(8);
        $indexSheet->getColumnDimension('B')->setWidth(20);
        $indexSheet->getColumnDimension('C')->setWidth(30);

        $usedTitles = ['รายชื่อ' => true];
        $rr = 2;
        foreach ($roster as $ss) {
            $student = $ss->student;
            $indexSheet->setCellValue("A{$rr}", $ss->student_number);
            $indexSheet->setCellValue("B{$rr}", $student->student_code ?? '');
            $indexSheet->setCellValue("C{$rr}", trim(($student->thai_prefix ?? '') . ($student->thai_firstname ?? '') . ' ' . ($student->thai_lastname ?? '')));
            $rr++;

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->uniqueSheetTitle($student->student_code ?: ('student-' . $student->student_id), $usedTitles));
            $this->buildTranscriptSheet($sheet, $student);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $tmpPath = tempnam(sys_get_temp_dir(), 'transcript_bulk_template') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $filename = 'แบบฟอร์มนำเข้าเกรดรวมทั้งห้อง_' . str_replace('/', '-', $section->full_name) . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    // ทำให้ชื่อชีตปลอดภัย/ไม่ซ้ำกัน — Excel ห้ามใช้ : \ / ? * [ ] และจำกัดไม่เกิน 31 ตัวอักษร,
    // ชื่อซ้ำ (เช่น เลขประจำตัวนักเรียนซ้ำกันโดยไม่ตั้งใจ) จะเติมลำดับต่อท้ายกันชนกัน
    private function uniqueSheetTitle(string $raw, array &$usedTitles): string
    {
        $title = preg_replace('/[:\\\\\/?*\[\]]/u', '-', $raw);
        $title = mb_substr($title !== '' ? $title : 'sheet', 0, 31);

        $base = $title;
        $i = 2;
        while (isset($usedTitles[$title])) {
            $suffix = "-{$i}";
            $title = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
            $i++;
        }
        $usedTitles[$title] = true;
        return $title;
    }

    // รับไฟล์ Excel เกรดรวมทั้งห้องที่กรอกมา แล้วรันคำสั่งนำเข้าให้หลายคนพร้อมกัน
    // ครอบทั้งฟังก์ชันด้วย try/catch เพราะการอัปโหลดไฟล์มีจุดพังได้หลายจุด (validate() เองก็แตะไฟล์
    // เพื่อเดา mime type, store() ก็แตะไฟล์อีกที) — ถ้าไฟล์ชั่วคราวหายไปกลางทาง (เช่น เบราว์เซอร์/เครื่อง
    // บางเครื่องมีปัญหา) อยากให้ขึ้นข้อความแจ้งเตือนสวยๆ ไม่ใช่หน้า error ระบบ ยกเว้น validation error ปกติ
    // (เช่น ยังไม่เลือกไฟล์) ที่ต้องปล่อยผ่านให้ Laravel จัดการแบบเดิม (redirect back พร้อม $errors)
    public function importBulkUpload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx',
            ], [
                'file.required' => 'กรุณาเลือกไฟล์ Excel',
                'file.mimes' => 'ไฟล์ต้องเป็นสกุล .xlsx เท่านั้น',
            ]);

            set_time_limit(0);

            $uploaded = $request->file('file');
            if (! $uploaded || ! $uploaded->isValid()) {
                return back()->with('error', 'อัปโหลดไฟล์ไม่สำเร็จ (ไฟล์อาจเสียหายหรือใหญ่เกินไป) กรุณาเลือกไฟล์แล้วลองใหม่อีกครั้ง');
            }

            $fullPath = $this->moveUploadedFileToTemp($uploaded);
            if (! $fullPath) {
                return back()->with('error', 'ระบบเข้าถึงไฟล์ที่อัปโหลดไม่ได้ (มักเกิดจากโปรแกรมป้องกันไวรัสสแกนไฟล์ค้างอยู่ พบบ่อยกับไฟล์ขนาดใหญ่บน Windows) กรุณาลองใหม่อีกครั้ง หรือปิด real-time protection ชั่วคราวแล้วลองใหม่');
            }

            $options = ['file' => $fullPath];
            if ($request->boolean('dry_run')) {
                $options['--dry-run'] = true;
            }

            Artisan::call('import:transcript-bulk', $options);
            $output = Artisan::output();

            @unlink($fullPath);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'นำเข้าไฟล์ไม่สำเร็จ: ' . $e->getMessage());
        }

        return back()->with('transcript_import_output', $output);
    }

    // อัปเดตเกรด
    public function updateGrade(Request $request, $gradeId)
    {
        $grade = FinalGrade::findOrFail($gradeId);
        $score = $request->total_score;
        $gradeInfo = FinalGrade::calculateGrade($score);

        $manualGrade = $request->grade !== '' ? $request->grade : null;
        $gradeMap = ['4'=>4.0,'3.5'=>3.5,'3'=>3.0,'2.5'=>2.5,'2'=>2.0,'1.5'=>1.5,'1'=>1.0,'0'=>0.0,'I'=>0.0,'W'=>0.0,'S'=>4.0,'U'=>0.0];
        $finalGrade    = $manualGrade ?? $gradeInfo['grade'];
        $finalGpaPoint = $manualGrade !== null ? ($gradeMap[$manualGrade] ?? $gradeInfo['gpa']) : $gradeInfo['gpa'];

        $grade->update([
            'total_score' => $score,
            'grade'       => $finalGrade,
            'gpa_point'   => $finalGpaPoint,
            'remark'      => $score >= 50 ? 'ผ่าน' : 'ไม่ผ่าน',
        ]);
        return redirect()->back()->with('success', 'แก้ไขเกรดสำเร็จ');
    }

    // ลบเกรด
    public function destroyGrade($gradeId)
    {
        FinalGrade::findOrFail($gradeId)->delete();
        return redirect()->back()->with('success', 'ลบเกรดสำเร็จ');
    }

    // Export score sheet to Excel
    public function exportScoreExcel($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level',
            'classSection.semester.academicYear', 'scoreCategories'])->findOrFail($assignId);

        $students = \App\Models\Academic\StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')->get();

        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        $finalGrades = FinalGrade::where('assign_id', $assignId)->get()->keyBy('student_id');

        $filename = 'คะแนน_' . $assign->subject->code . '_' . $assign->classSection->level->name . $assign->classSection->section_number . '.xls';

        $html = view('academic.score_excel', compact('assign', 'students', 'categories', 'scoreMatrix', 'finalGrades'))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . urlencode($filename) . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    private function buildTranscriptData($studentId): array
    {
        $allGrades = FinalGrade::with([
                'teachingAssign.subject',
                'teachingAssign.classSection.level',
                'semester.academicYear',
            ])
            ->where('student_id', $studentId)
            ->orderBy('semester_id')
            ->get();

        $grades = $allGrades->groupBy(fn($g) =>
            ($g->semester->academicYear->year_name ?? '') . '|' .
            ($g->teachingAssign->classSection->level->name ?? '') . '|' .
            ($g->semester->semester_name ?? '')
        );

        $totalCredits = 0;
        $totalPoints  = 0;
        foreach ($allGrades as $g) {
            $credits = $g->teachingAssign->subject->credits ?? 0;
            $totalCredits += $credits;
            $totalPoints  += ($g->gpa_point ?? 0) * $credits;
        }
        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

        return [$grades, $gpa, $totalCredits];
    }

    // ผลการเรียนรายห้อง
    public function sectionReport($sectionId)
    {
        $section = ClassSection::with(['level', 'semester.academicYear'])->findOrFail($sectionId);

        $students = StudentSection::with('student')
            ->where('section_id', $sectionId)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')
            ->get();

        $grades = FinalGrade::with('teachingAssign.subject')
            ->whereHas('teachingAssign', fn($q) => $q->where('section_id', $sectionId))
            ->where('semester_id', $section->semester_id)
            ->get()
            ->groupBy('student_id');

        return view('academic.section_grades', compact('section', 'students', 'grades'));
    }

    // พิมพ์ใบบันทึกคะแนน
    public function printScoreSheet($assignId)
    {
        $assign = TeachingAssign::with(['personnel', 'subject', 'classSection.level',
            'classSection.semester.academicYear', 'scoreCategories'])->findOrFail($assignId);

        $students = StudentSection::with('student')
            ->where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->orderBy('student_number')->get();

        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();

        $scoreMatrix = [];
        foreach ($categories as $cat) {
            foreach ($cat->studentScores as $sc) {
                $scoreMatrix[$sc->student_id][$cat->category_id] = $sc->score;
            }
        }

        $finalGrades = FinalGrade::where('assign_id', $assignId)
            ->get()->keyBy('student_id');

        return view('academic.grade_print', compact('assign', 'students', 'categories', 'scoreMatrix', 'finalGrades'));
    }

    // รายงาน GPA
    public function gpaReport(Request $request)
    {
        $semesterId = $request->semester_id ?? Semester::where('is_current', true)->value('semester_id');
        $semesters = Semester::with('academicYear')->orderedByRecency()->get();

        $gpaData = DB::table('final_grades as fg')
            ->join('teaching_assigns as ta', 'fg.assign_id', '=', 'ta.assign_id')
            ->join('subjects as sub', 'ta.subject_id', '=', 'sub.subject_id')
            ->join('students as s', 'fg.student_id', '=', 's.student_id')
            ->join('student_sections as ss', function($j) {
                $j->on('ss.student_id', '=', 's.student_id')
                  ->on('ss.section_id', '=', 'ta.section_id');
            })
            ->where('fg.semester_id', $semesterId)
            ->select(
                's.student_id', 's.student_code', 's.thai_firstname', 's.thai_lastname',
                'ss.student_number',
                DB::raw('ROUND(SUM(fg.gpa_point * sub.credits) / NULLIF(SUM(sub.credits), 0), 2) as gpa'),
                DB::raw('SUM(sub.credits) as total_credits')
            )
            ->groupBy('s.student_id', 's.student_code', 's.thai_firstname', 's.thai_lastname', 'ss.student_number')
            ->orderBy('gpa', 'desc')
            ->get();

        return view('academic.gpa_report', compact('gpaData', 'semesters', 'semesterId'));
    }
    
   // ==========================================
    // พิมพ์ใบ ปพ.1 (และกรองเทอมตามที่ติ๊กเลือก)
    // ==========================================
    public function printPor1(Request $request, $studentId)
    {
        // 1. ดึงข้อมูล
        $student = Student::with('education')->findOrFail($studentId);
        $father = \App\Models\StudentFamily::where('student_id', $studentId)->where('guardian_type', 'บิดา')->first();
        $mother = \App\Models\StudentFamily::where('student_id', $studentId)->where('guardian_type', 'มารดา')->first();
        
        $docNumber = StudentDocNumber::where('student_id', $studentId)->latest()->first()
            ?? (object)['doc_set' => '', 'doc_number' => ''];

        // ปกปิดชุดที่/เลขที่ตามระเบียบ — ติ๊กจาก Modal ตั้งค่าการพิมพ์
        $hideDocNumber = $request->boolean('hide_doc_number');

        // 2. รับค่าเทอมที่ติ๊กมาจาก Modal
        $filterActive = $request->has('filter_active'); // เช็คว่าส่งมาจากหน้าป๊อปอัปหรือไม่
        $selectedSemesters = $request->input('semesters', []); 
        
        // 3. ดึงเกรดทั้งหมด
        $allGrades = FinalGrade::with(['teachingAssign.subject', 'teachingAssign.classSection.level', 'semester.academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('semester_id')
            ->get();
            
        $yearGroups = [];
        
        $shownSemesterIds = [];

        foreach ($allGrades as $grade) {
            $year = $grade->semester->academicYear->year_name ?? '';
            $term = $grade->semester->semester_name ?? '';
            $level = $grade->teachingAssign->classSection->level->name ?? '';

            $semKey = $year . '/' . $term;

            // 🛑 ถ้าเปิดใช้งานตัวกรอง (ผ่าน Modal) และเทอมนี้ไม่อยู่ในรายการที่ติ๊ก -> ข้ามทันที!
            if ($filterActive && !in_array($semKey, $selectedSemesters)) {
                continue;
            }

            $shownSemesterIds[] = $grade->semester_id;

            if (!isset($yearGroups[$year])) {
                $yearGroups[$year] = ['year' => $year, 'level' => $level, 'semesters' => []];
            }
            if (!isset($yearGroups[$year]['semesters'][$term])) {
                $yearGroups[$year]['semesters'][$term] = [];
            }
            
            $yearGroups[$year]['semesters'][$term][] = $grade;
        }

        // วันอนุมัติการจบ
        $studentSectionIds = \App\Models\Academic\StudentSection::where('student_id', $studentId)->pluck('section_id');
        $pp2Setting = \App\Models\Pp2SectionSetting::whereIn('section_id', $studentSectionIds)->first();
        $approveDate = '';
        if ($request->filled('approve_date')) {
            $approveDate = $this->formatThaiDate($request->input('approve_date'));
        } elseif ($pp2Setting?->issued_date) {
            $approveDate = $this->formatThaiDate($pp2Setting->issued_date->format('Y-m-d'));
        }

        // วันออกจากโรงเรียน และ สาเหตุ
        $promotion = \App\Models\Academic\Promotion::where('student_id', $studentId)->latest('promo_date')->first();
        $leaveDate   = $promotion?->promo_date ? $this->formatThaiDate($promotion->promo_date->format('Y-m-d')) : '';
        $leaveReason = $promotion?->remark ?? '';

        $school = \App\Models\Academic\Pp2Setting::mergedSchoolConfig();

        // ผลการประเมิน (อ่าน/คุณลักษณะ/กิจกรรม) ของภาคเรียนล่าสุดที่แสดงในเอกสาร
        $latestSemId = !empty($shownSemesterIds) ? max($shownSemesterIds) : null;
        $assessment = \App\Models\Academic\StudentAssessment::where('student_id', $studentId)
                ->when($latestSemId, fn($q) => $q->where('semester_id', $latestSemId))
                ->first()
            ?? \App\Models\Academic\StudentAssessment::where('student_id', $studentId)
                ->orderByDesc('semester_id')->first();

        // คะแนน O-NET (ของปีการศึกษาล่าสุดที่นักเรียนสอบ)
        $onetScores = \App\Models\Academic\OnetScore::where('student_id', $studentId)
            ->orderByDesc('year_id')
            ->get();
        $onetYearId = $onetScores->first()?->year_id;
        $onetScores = $onetScores->where('year_id', $onetYearId)->keyBy('subject');

        return view('academic.por1_print', compact('student', 'father', 'mother', 'yearGroups', 'docNumber', 'hideDocNumber', 'approveDate', 'leaveDate', 'leaveReason', 'school', 'assessment', 'onetScores'));
    }

    private function formatThaiDate(string $dateStr): string
    {
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                   'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $d = \Carbon\Carbon::parse($dateStr);
        return $d->day . ' ' . $months[$d->month] . ' ' . ($d->year + 543);
    }
}