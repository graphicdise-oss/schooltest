<?php

namespace App\Console\Commands;

use App\Models\Academic\ClassAttendance;
use App\Models\Academic\TeachingAssign;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * นำเข้าผลการเช็คชื่อ (ClassAttendance) จากไฟล์ Excel ที่ครูกรอกออฟไลน์ — ดาวน์โหลดจาก
 * AttendanceController::exportTemplate() ไฟล์เป็นเวิร์กบุ๊ก 1 ชีตต่อ 1 วิชา-ห้อง-เดือน (assign) มีแถว
 * "รหัสอ้างอิง" (assign_id) กับ "เดือนที่อ้างอิง" (YYYY-MM) บอกว่าชีตนั้นเป็นของวิชา-ห้อง/เดือนไหน คอลัมน์วันที่
 * เป็นเลขวันของเดือน (1-31) ไม่ใช่วันที่เต็ม ต้องประกอบกับเดือน/ปีที่อ้างอิงถึงจะได้วันที่จริง ช่องไหน
 * เว้นว่างไว้ = ไม่เช็คชื่อวันนั้น (ข้ามเงียบๆ ไม่ error) ช่องไหนกรอกสถานะที่ไม่ตรงกับ STATUSES = คำเตือน
 */
class ImportAttendanceFromExcel extends Command
{
    protected $signature = 'import:attendance
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'นำเข้าผลการเช็คชื่อจากไฟล์ Excel ที่ครูกรอกออฟไลน์ (ดาวน์โหลดจากหน้าเช็คชื่อ/ปรับสถานะการมาเรียน)';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!$path || !is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        [$sheetsData, $warnings] = $this->parseFile($path);

        if (empty($sheetsData)) {
            $this->error('ไม่พบข้อมูลเช็คชื่อในไฟล์ที่อัปโหลด ตรวจสอบว่าใช้แบบฟอร์มที่ถูกต้อง (แต่ละชีตต้องมีแถว "รหัสอ้างอิง")');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');

        $totals = ['saved' => 0, 'skipped' => 0];
        $perAssign = [];

        try {
            DB::transaction(function () use ($sheetsData, &$totals, &$perAssign, &$warnings) {
                foreach ($sheetsData as $sheetLabel => $rows) {
                    $result = $this->applySheet($sheetLabel, $rows, $warnings);
                    if ($result === null) {
                        continue;
                    }
                    $totals['saved'] += $result['saved'];
                    $totals['skipped'] += $result['skipped'];
                    $perAssign[$sheetLabel] = $result;
                }

                if ($this->option('dry-run')) {
                    throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== '__DRY_RUN_ROLLBACK__') {
                throw $e;
            }
        }

        $this->newLine();
        foreach ($perAssign as $label => $result) {
            $this->line("  - {$label}: บันทึก {$result['saved']} ช่อง" . ($result['skipped'] ? ", ข้าม {$result['skipped']} ช่อง (สิทธิ์/ค่าไม่ถูกต้อง)" : ''));
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะบันทึกผลเช็คชื่อรวม: ' : 'บันทึกผลเช็คชื่อรวมสำเร็จ: ') . "{$totals['saved']} ช่อง (" . count($perAssign) . " วิชา-ห้อง)");

        if (!empty($warnings)) {
            $this->newLine();
            $this->warn('แถว/ช่องที่ข้าม/อ่านไม่ได้:');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกบันทึกจริง หากตรวจสอบแล้วถูกต้อง ให้กดนำเข้าอีกครั้งแบบไม่ทดสอบ');
        }

        return self::SUCCESS;
    }

    /**
     * บันทึกข้อมูลของ 1 ชีต (1 assign) ลง DB จริง — เช็คสิทธิ์ก่อน (เฉพาะแอดมิน/ครูประจำวิชานั้น)
     * แล้วจับคู่รหัสนักศึกษากับ Student ใน DB ทีเดียว คืน null ถ้าทั้งชีตข้ามหมด (ไม่มี assign/ไม่มีสิทธิ์)
     */
    private function applySheet(string $sheetLabel, array $rows, array &$warnings): ?array
    {
        $assignId = $rows['assign_id'];
        $assign = TeachingAssign::find($assignId);
        if (!$assign) {
            $warnings[] = "ชีต \"{$sheetLabel}\": ไม่พบวิชา-ห้อง (รหัสอ้างอิง {$assignId}) ในระบบ — ข้ามทั้งชีต";
            return null;
        }

        $user = auth()->user();
        if ($user && !$user->isAdmin() && $user->personnel_id !== $assign->personnel_id) {
            $warnings[] = "ชีต \"{$sheetLabel}\": ไม่มีสิทธิ์บันทึกวิชานี้ (เฉพาะครูประจำวิชาเท่านั้น) — ข้ามทั้งชีต";
            return null;
        }

        $studentCodes = collect($rows['entries'])->pluck('student_code')->unique()->values();
        $students = Student::whereIn('student_code', $studentCodes)->get()->keyBy('student_code');

        $saved = 0;
        $skipped = 0;
        foreach ($rows['entries'] as $entry) {
            $student = $students->get($entry['student_code']);
            if (!$student) {
                $warnings[] = "ชีต \"{$sheetLabel}\": ไม่พบนักเรียนเลขประจำตัว \"{$entry['student_code']}\" — ข้ามแถวนี้";
                $skipped++;
                continue;
            }
            ClassAttendance::updateOrCreate(
                ['assign_id' => $assignId, 'student_id' => $student->student_id, 'class_date' => $entry['date']],
                ['status' => $entry['status']]
            );
            $saved++;
        }

        return ['saved' => $saved, 'skipped' => $skipped];
    }

    /**
     * โหลดไฟล์แล้ววนอ่านทีละชีต — ชีตไหนไม่มีแถว "รหัสอ้างอิง" ถือว่าไม่ใช่ชีตข้อมูล ข้ามเงียบๆ
     * คืนค่า [ [ 'ชื่อชีต' => ['assign_id'=>.., 'entries'=>[['student_code','date','status'], ...]] ], warnings ]
     */
    private function parseFile(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheetsData = [];
        $warnings = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $assignId = $this->extractLabeledValue($sheet, 'รหัสอ้างอิง (ห้ามแก้ไข)');
            if ($assignId === null) {
                continue; // ไม่ใช่ชีตข้อมูล (เช่นไม่ได้มาจากแบบฟอร์มนี้) ข้ามเงียบๆ
            }
            $monthRef = $this->extractLabeledValue($sheet, 'เดือนที่อ้างอิง (ห้ามแก้ไข)');
            if (!$monthRef || !preg_match('/^(\d{4})-(\d{1,2})$/', $monthRef, $mm)) {
                $warnings[] = "ชีต \"{$sheet->getTitle()}\": อ่านค่า \"เดือนที่อ้างอิง\" ไม่ได้ (ต้องเป็นรูปแบบ YYYY-MM) — ข้ามทั้งชีต";
                continue;
            }
            [$refYear, $refMonth] = [(int) $mm[1], (int) $mm[2]];

            $headerRow = $this->findHeaderRow($sheet);
            if ($headerRow === null) {
                $warnings[] = "ชีต \"{$sheet->getTitle()}\": หาแถวหัวตาราง (เลขที่/เลขประจำตัวนักเรียน/ชื่อ-สกุล) ไม่พบ — ข้ามทั้งชีต";
                continue;
            }

            $dateColumns = $this->readDateColumns($sheet, $headerRow, $refYear, $refMonth);
            if (empty($dateColumns)) {
                $warnings[] = "ชีต \"{$sheet->getTitle()}\": ไม่พบคอลัมน์วันที่ในแถวหัวตาราง — ข้ามทั้งชีต";
                continue;
            }

            $entries = [];
            $highestRow = $sheet->getHighestRow();
            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                $studentCode = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
                if ($studentCode === '') {
                    continue; // แถวว่าง ข้ามเงียบๆ
                }
                foreach ($dateColumns as $col => $date) {
                    $raw = trim((string) ($sheet->getCell("{$col}{$row}")->getValue() ?? ''));
                    if ($raw === '') {
                        continue; // ไม่ได้กรอกวันนี้ ข้ามเงียบๆ (ไม่ใช่ error)
                    }
                    if (!in_array($raw, ClassAttendance::STATUSES, true)) {
                        $warnings[] = "ชีต \"{$sheet->getTitle()}\" แถว {$row} คอลัมน์ {$col}: ค่า \"{$raw}\" ไม่ใช่สถานะที่รู้จัก (" . implode('/', ClassAttendance::STATUSES) . ") — ข้ามช่องนี้";
                        continue;
                    }
                    $entries[] = ['student_code' => $studentCode, 'date' => $date, 'status' => $raw];
                }
            }

            if (empty($entries)) {
                continue; // ชีตนี้เป็นแบบฟอร์มถูกต้องแต่ยังไม่มีใครกรอกอะไรเลย ข้ามเงียบๆ
            }

            $sheetsData[$sheet->getTitle()] = ['assign_id' => $assignId, 'entries' => $entries];
        }

        return [$sheetsData, $warnings];
    }

    // สแกนคอลัมน์ A แถว 1-15 หาแถวที่ตรงกับป้าย $label เป๊ะๆ แล้วอ่านค่าที่คอลัมน์ B ของแถวเดียวกัน
    private function extractLabeledValue($sheet, string $label): ?string
    {
        $highestRow = min($sheet->getHighestRow(), 15);
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellLabel = trim((string) ($sheet->getCell("A{$row}")->getValue() ?? ''));
            if ($cellLabel === $label) {
                $value = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
                return $value !== '' ? $value : null;
            }
        }
        return null;
    }

    // หาแถวหัวตาราง (A=เลขที่, B=เลขประจำตัวนักเรียน) ในช่วง 12 แถวแรก
    private function findHeaderRow($sheet): ?int
    {
        $highestRow = min($sheet->getHighestRow(), 12);
        for ($row = 1; $row <= $highestRow; $row++) {
            $a = trim((string) ($sheet->getCell("A{$row}")->getValue() ?? ''));
            $b = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
            if ($a === 'เลขที่' && $b === 'เลขประจำตัวนักเรียน') {
                return $row;
            }
        }
        return null;
    }

    // อ่านคอลัมน์ D เป็นต้นไปของแถวหัวตาราง เป็นเลขวันของเดือน (1-31) แล้วประกอบเป็นวันที่จริงด้วย $year/$month
    // ที่อ่านมาจาก "เดือนที่อ้างอิง" — หยุดอ่านเมื่อเจอคอลัมน์ที่ไม่ใช่ตัวเลข (ชนคอลัมน์สรุป รวม/ขาด/ร้อยละ)
    private function readDateColumns($sheet, int $headerRow, int $year, int $month): array
    {
        $columns = [];
        $highestCol = $sheet->getHighestColumn($headerRow);
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        for ($colIndex = 4; $colIndex <= $highestColIndex; $colIndex++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $value = $sheet->getCell("{$col}{$headerRow}")->getValue();
            if ($value === null || trim((string) $value) === '' || !is_numeric($value)) {
                continue;
            }
            $day = (int) $value;
            if ($day < 1 || $day > 31) {
                continue;
            }
            $columns[$col] = sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        return $columns;
    }
}
