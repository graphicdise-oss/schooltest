<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ParsesWideTranscriptSheet;
use App\Console\Commands\Concerns\WritesTranscriptGrades;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * นำเข้าเกรดรวม (Transcript) ของนักเรียน "หลายคนพร้อมกัน" (เช่น ทั้งห้อง) จากไฟล์ Excel เดียว —
 * ไฟล์นี้เป็นเวิร์กบุ๊กหลายชีต ชีตละ 1 คน (แต่ละชีตหน้าตาเดียวกับแบบฟอร์มของ import:transcript ทุก
 * ประการ แค่มีแถว "ชื่อ-สกุล"/"รหัสนักศึกษา" เพิ่มมาบอกว่าชีตนี้เป็นของใคร) — ดู GradeController::
 * importBulkTemplate()/buildTranscriptSheet() สำหรับตอนสร้างไฟล์
 */
class ImportTranscriptBulkFromExcel extends Command
{
    use WritesTranscriptGrades;
    use ParsesWideTranscriptSheet;

    protected $signature = 'import:transcript-bulk
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'นำเข้าเกรดรวม (Transcript) ของนักเรียนหลายคน/ทั้งห้องพร้อมกันจากไฟล์ Excel เดียว '
        . '(เวิร์กบุ๊กหลายชีต ชีตละ 1 คน มีแถวเลขประจำตัวนักเรียนบอกเจ้าของแต่ละชีต) — '
        . 'ถ้าจะนำเข้าทีละคนไฟล์เดียว ใช้ import:transcript แทน';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!$path || !is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        [$rowsByStudentCode, $activitiesByStudentCode, $warnings] = $this->parseFile($path);

        if (empty($rowsByStudentCode) && empty($activitiesByStudentCode)) {
            $this->error('ไม่พบข้อมูลผลการเรียนในไฟล์ที่อัปโหลด ตรวจสอบว่าใช้แบบฟอร์มที่ถูกต้อง (แต่ละชีตต้องมีแถว "รหัสนักศึกษา" และแถว "ปีการศึกษา")');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
            return self::FAILURE;
        }

        // เอาเลขประจำตัวทั้งหมดที่เจอในไฟล์ (ทั้งตารางวิชาและกิจกรรม) มาหาตัวนักเรียนใน DB ทีเดียว
        $studentCodes = collect(array_keys($rowsByStudentCode))
            ->merge(array_keys($activitiesByStudentCode))
            ->unique()->values();

        $students = Student::whereIn('student_code', $studentCodes)->get()->keyBy('student_code');

        $notFound = $studentCodes->diff($students->keys());
        foreach ($notFound as $code) {
            $warnings[] = "ไม่พบนักเรียนเลขประจำตัว \"{$code}\" ในระบบ — ข้ามข้อมูลของเลขนี้ทั้งหมด";
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');
        $this->info('พบนักเรียนที่มีข้อมูลในไฟล์: ' . $students->count() . ' คน' . ($notFound->count() ? " (หาไม่เจอ {$notFound->count()} คน ดูคำเตือนด้านล่าง)" : ''));

        $totals = ['created' => 0, 'updated' => 0, 'actCreated' => 0, 'actUpdated' => 0];
        $perStudent = [];

        try {
            DB::transaction(function () use ($students, $rowsByStudentCode, $activitiesByStudentCode, &$totals, &$perStudent, &$warnings) {
                foreach ($students as $code => $student) {
                    $data = $rowsByStudentCode[$code] ?? [];
                    $activities = $activitiesByStudentCode[$code] ?? [];
                    if (empty($data) && empty($activities)) {
                        continue;
                    }

                    $result = $this->writeStudentTranscript($student, $data, $activities, $warnings);
                    foreach ($totals as $k => $v) {
                        $totals[$k] += $result[$k];
                    }
                    $perStudent[$code] = $result;
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
        foreach ($perStudent as $code => $result) {
            $student = $students[$code];
            $label = "{$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} ({$code})";
            $this->line("  - {$label}: สร้างใหม่ {$result['created']} วิชา, อัปเดต {$result['updated']} วิชา"
                . ($result['actCreated'] || $result['actUpdated'] ? ", กิจกรรมใหม่ {$result['actCreated']}, อัปเดตกิจกรรม {$result['actUpdated']}" : ''));
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะสร้างผลการเรียนใหม่รวม: ' : 'สร้างผลการเรียนใหม่รวมสำเร็จ: ') . "{$totals['created']} รายวิชา (" . count($perStudent) . " คน)");
        if ($totals['updated'] > 0) {
            $this->info(($dryRun ? 'จะอัปเดตของเดิมรวม: ' : 'อัปเดตของเดิมรวมสำเร็จ: ') . "{$totals['updated']} รายวิชา");
        }
        if ($totals['actCreated'] > 0 || $totals['actUpdated'] > 0) {
            $this->info(($dryRun ? 'จะบันทึกกิจกรรมพัฒนาผู้เรียนใหม่รวม: ' : 'บันทึกกิจกรรมพัฒนาผู้เรียนใหม่รวมสำเร็จ: ') . "{$totals['actCreated']} รายการ");
            if ($totals['actUpdated'] > 0) {
                $this->info(($dryRun ? 'จะอัปเดตกิจกรรมเดิมรวม: ' : 'อัปเดตกิจกรรมเดิมรวมสำเร็จ: ') . "{$totals['actUpdated']} รายการ");
            }
        }

        if (!empty($warnings)) {
            $this->newLine();
            $this->warn('แถวที่ข้าม/อ่านไม่ได้:');
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
     * โหลดไฟล์แล้ววนอ่านทีละชีต — แต่ละชีตแยกอิสระ ใช้ parseTranscriptSheet() (ตัวเดียวกับ
     * import:transcript) อ่านตารางวิชา/กิจกรรมแบบ "3 ปีเคียงกัน" ตามปกติ แล้วหาว่าชีตนี้เป็นของใคร
     * จากแถว "รหัสนักศึกษา" ที่ GradeController::buildTranscriptSheet() เขียนไว้ให้ทุกชีต — ชีตไหน
     * ไม่มีทั้งตารางวิชา/กิจกรรมเลย (เช่น ชีตสารบัญ/รายชื่อ) ข้ามไปเงียบๆ ไม่ใช่ error
     */
    private function parseFile(string $path): array
    {
        $spreadsheet = IOFactory::load($path);

        $data = [];
        $activities = [];
        $warnings = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            [$sheetData, $sheetActivities, $sheetWarnings] = $this->parseTranscriptSheet($sheet);
            if (empty($sheetData) && empty($sheetActivities)) {
                continue;
            }

            $code = $this->extractStudentCodeFromSheet($sheet);
            if (!$code) {
                $warnings[] = "ชีต \"{$sheet->getTitle()}\": อ่านค่า \"รหัสนักศึกษา\" ไม่ได้ — ข้ามทั้งชีต";
                continue;
            }

            if (isset($data[$code]) || isset($activities[$code])) {
                $warnings[] = "รหัสนักศึกษา {$code} มีมากกว่า 1 ชีตในไฟล์นี้ (ชีต \"{$sheet->getTitle()}\") — ใช้ข้อมูลจากชีตนี้ทับของชีตก่อนหน้า";
            }
            if (!empty($sheetData)) {
                $data[$code] = $sheetData;
            }
            if (!empty($sheetActivities)) {
                $activities[$code] = $sheetActivities;
            }
            foreach ($sheetWarnings as $w) {
                $warnings[] = "ชีต \"{$sheet->getTitle()}\" (รหัส {$code}): {$w}";
            }
        }

        return [$data, $activities, $warnings];
    }

    // หาค่า "รหัสนักศึกษา" ของชีตนี้ — สแกนคอลัมน์ A แถว 1-15 หาแถวที่ตรงกับป้าย "รหัสนักศึกษา" เป๊ะๆ
    // (เขียนไว้โดย GradeController::writeIdentityRow()) แล้วอ่านค่าที่คอลัมน์ B ของแถวเดียวกัน
    private function extractStudentCodeFromSheet($sheet): ?string
    {
        $highestRow = min($sheet->getHighestRow(), 15);
        for ($row = 1; $row <= $highestRow; $row++) {
            $label = trim((string) ($sheet->getCell("A{$row}")->getValue() ?? ''));
            if ($label === 'รหัสนักศึกษา') {
                $value = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
                return $value !== '' ? $value : null;
            }
        }
        return null;
    }
}
