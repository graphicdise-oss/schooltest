<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\WritesTranscriptGrades;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * นำเข้าเกรดรวม (Transcript) ของนักเรียน "หลายคนพร้อมกัน" (เช่น ทั้งห้อง) จากไฟล์ Excel เดียว —
 * ต่างจาก import:transcript (ทีละคน, 3 ปีเคียงกันเป็นคอลัมน์) ตรงที่ไฟล์นี้เป็นตารางแถวเรียงยาว
 * (flat table) คอลัมน์แรกสุดคือ "เลขประจำตัวนักเรียน" เพื่อบอกว่าแต่ละแถวเป็นของใคร —
 * ดูโครงสร้างคอลัมน์ที่คาดหวังในคอมเมนต์ parseFile()
 */
class ImportTranscriptBulkFromExcel extends Command
{
    use WritesTranscriptGrades;

    protected $signature = 'import:transcript-bulk
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'นำเข้าเกรดรวม (Transcript) ของนักเรียนหลายคน/ทั้งห้องพร้อมกันจากไฟล์ Excel '
        . '(ตารางแถวเรียงยาว มีคอลัมน์เลขประจำตัวนักเรียนระบุเจ้าของแต่ละแถว) — '
        . 'ถ้าจะนำเข้าทีละคนแบบ 3 ปีเคียงกัน ใช้ import:transcript แทน';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!$path || !is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        [$rowsByStudentCode, $activitiesByStudentCode, $warnings] = $this->parseFile($path);

        if (empty($rowsByStudentCode) && empty($activitiesByStudentCode)) {
            $this->error('ไม่พบข้อมูลผลการเรียนในไฟล์ที่อัปโหลด ตรวจสอบว่าใช้แบบฟอร์มที่ถูกต้อง (ต้องมีคอลัมน์ "เลขประจำตัวนักเรียน")');
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
     * อ่านไฟล์ Excel แบบตารางแถวเรียงยาว (flat table) มี 2 ตารางแยกกัน คั่นด้วยแถว "ตารางกิจกรรมพัฒนาผู้เรียน":
     *  1) ตารางผลการเรียนรายวิชา — แถวหัวคอลัมน์คือแถวแรกที่คอลัมน์ A ขึ้นต้นด้วย "เลขประจำตัวนักเรียน"
     *     คอลัมน์ที่คาดหวัง (เรียงตามลำดับ ไม่สนชื่อหัวคอลัมน์เป๊ะๆ): เลขประจำตัวนักเรียน, ปีการศึกษา,
     *     ระดับชั้น, ภาคเรียน, รหัสวิชา : ชื่อวิชา, หน่วยกิต, เกรด(0-4)
     *  2) ตารางกิจกรรมพัฒนาผู้เรียน — เริ่มที่แถวซึ่งคอลัมน์ A ขึ้นต้นด้วย "ตารางกิจกรรมพัฒนาผู้เรียน" เป๊ะๆ
     *     แถวถัดไปเป็นแถวหัวคอลัมน์ (เหมือนกันแต่คอลัมน์ 5-7 เป็น ชื่อกิจกรรม, เวลา(ชั่วโมง), ผลการประเมิน)
     *
     * คืนค่า [ผลการเรียนแยกตามเลขประจำตัว, กิจกรรมแยกตามเลขประจำตัว, คำเตือน] — แต่ละ "ผลการเรียนของคนหนึ่ง"
     * มีโครงสร้างแบบเดียวกับที่ import:transcript ใช้ (year => ['level'=>, 'semesters'=>[sem => [[code,name,credit,grade],...]]])
     * เพื่อให้ WritesTranscriptGrades::writeStudentTranscript() เรียกใช้ร่วมกันได้ทันที
     */
    private function parseFile(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $get = fn (int $col, int $row) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));

        $warnings = [];

        $subjectHeaderRow = null;
        for ($row = 1; $row <= $highestRow; $row++) {
            if (str_starts_with($get(1, $row), 'เลขประจำตัวนักเรียน')) {
                $subjectHeaderRow = $row;
                break;
            }
        }
        if ($subjectHeaderRow === null) {
            return [[], [], ['ไม่พบแถวหัวตาราง (คอลัมน์แรกต้องขึ้นต้นด้วย "เลขประจำตัวนักเรียน") ในไฟล์นี้เลย']];
        }

        $activityDividerRow = null;
        for ($row = $subjectHeaderRow + 1; $row <= $highestRow; $row++) {
            if (str_starts_with($get(1, $row), 'ตารางกิจกรรมพัฒนาผู้เรียน')) {
                $activityDividerRow = $row;
                break;
            }
        }
        $subjectEndRow = $activityDividerRow ? $activityDividerRow - 1 : $highestRow;

        $data = $this->readFlatSubjectRows($get, $subjectHeaderRow + 1, $subjectEndRow, $warnings);

        $activities = [];
        if ($activityDividerRow !== null) {
            // แถวถัดจากตัวคั่นคือแถวหัวคอลัมน์ของตารางกิจกรรม ข้ามไป 1 แถว
            $activities = $this->readFlatActivityRows($get, $activityDividerRow + 2, $highestRow, $warnings);
        }

        return [$data, $activities, $warnings];
    }

    private function readFlatSubjectRows(callable $get, int $fromRow, int $toRow, array &$warnings): array
    {
        $data = [];

        for ($row = $fromRow; $row <= $toRow; $row++) {
            $code = $get(1, $row);
            $yearRaw = $get(2, $row);
            $levelRaw = $get(3, $row);
            $semRaw = $get(4, $row);
            $subjectRaw = $get(5, $row);
            $creditRaw = $get(6, $row);
            $gradeRaw = $get(7, $row);

            if ($code === '' && $yearRaw === '' && $subjectRaw === '') {
                continue;
            }
            // แถวเริ่มต้นที่ฟอร์มใส่เลขประจำตัวไว้ให้แต่ยังไม่ได้กรอกอะไรต่อเลย (คนนั้นไม่ได้ใช้แถวนี้) — ข้ามแบบเงียบๆ
            if ($yearRaw === '' && $levelRaw === '' && $semRaw === '' && $subjectRaw === '' && $creditRaw === '' && $gradeRaw === '') {
                continue;
            }
            if ($code === '') {
                $warnings[] = "แถว {$row}: ไม่มีเลขประจำตัวนักเรียน — ข้าม";
                continue;
            }
            if (!preg_match('/^(\S+)\s*:\s*(.+)$/u', $subjectRaw, $m)) {
                $warnings[] = "แถว {$row} (เลขประจำตัว {$code}): อ่านรหัส/ชื่อวิชาไม่ได้จากค่า \"{$subjectRaw}\" (ต้องเป็นรูปแบบ \"รหัสวิชา : ชื่อวิชา\") — ข้าม";
                continue;
            }
            $subjectCode = trim($m[1]);
            $subjectName = trim($m[2]);

            if (!preg_match('/\d{4}/u', $yearRaw)) {
                $warnings[] = "แถว {$row} (เลขประจำตัว {$code}): ปีการศึกษา \"{$yearRaw}\" ต้องเป็นปี พ.ศ. 4 หลัก เช่น 2567 — ข้าม";
                continue;
            }
            $year = substr(preg_replace('/\D/u', '', $yearRaw), 0, 4);

            $level = $this->shortenLevel($levelRaw);
            if ($level === '') {
                $warnings[] = "แถว {$row} (เลขประจำตัว {$code}): ไม่มีระดับชั้น — ข้าม";
                continue;
            }

            if (!preg_match('/(\d+)/u', $semRaw, $sm)) {
                $warnings[] = "แถว {$row} (เลขประจำตัว {$code}): ภาคเรียน \"{$semRaw}\" ต้องมีตัวเลข (1 หรือ 2) — ข้าม";
                continue;
            }
            $sem = $sm[1];

            $credit = is_numeric($creditRaw) ? (float) $creditRaw : 0;

            if (!is_numeric($gradeRaw)) {
                $warnings[] = "แถว {$row} วิชา {$subjectCode} {$subjectName} (เลขประจำตัว {$code}): เกรด \"{$gradeRaw}\" ไม่ใช่ตัวเลข (0-4) — ข้าม";
                continue;
            }
            $grade = (float) $gradeRaw;
            if ($grade < 0 || $grade > 4) {
                $warnings[] = "แถว {$row} วิชา {$subjectCode} {$subjectName} (เลขประจำตัว {$code}): เกรด {$grade} ต้องอยู่ระหว่าง 0-4 — ข้าม";
                continue;
            }

            $data[$code][$year]['level'] = $level;
            $data[$code][$year]['semesters'][$sem][] = [$subjectCode, $subjectName, $credit, $grade];
        }

        return $data;
    }

    private function readFlatActivityRows(callable $get, int $fromRow, int $toRow, array &$warnings): array
    {
        $activities = [];

        for ($row = $fromRow; $row <= $toRow; $row++) {
            $code = $get(1, $row);
            $yearRaw = $get(2, $row);
            $levelRaw = $get(3, $row);
            $semRaw = $get(4, $row);
            $actName = $get(5, $row);
            $hoursRaw = $get(6, $row);
            $resultRaw = trim($get(7, $row));

            if ($code === '' && $yearRaw === '' && $actName === '') {
                continue;
            }
            // แถวเริ่มต้นที่ฟอร์มใส่เลขประจำตัว/ชื่อกิจกรรม/เวลาไว้ให้ล่วงหน้า แต่คนนั้นไม่ได้กรอกปี/ระดับ/เทอม/
            // ผลประเมินต่อเลย (ไม่ได้ใช้แถวนี้) — ข้ามแบบเงียบๆ ไม่ต้องเตือนทุกแถวที่เหลือของทุกคนที่ไม่ได้กรอกกิจกรรม
            if ($yearRaw === '' && $levelRaw === '' && $semRaw === '' && $resultRaw === '') {
                continue;
            }
            if ($code === '') {
                $warnings[] = "แถว {$row}: ไม่มีเลขประจำตัวนักเรียน — ข้าม";
                continue;
            }
            if ($actName === '') {
                $warnings[] = "แถว {$row} (เลขประจำตัว {$code}): ไม่มีชื่อกิจกรรม — ข้าม";
                continue;
            }
            if (!preg_match('/\d{4}/u', $yearRaw)) {
                $warnings[] = "แถว {$row} กิจกรรม {$actName} (เลขประจำตัว {$code}): ปีการศึกษา \"{$yearRaw}\" ต้องเป็นปี พ.ศ. 4 หลัก — ข้าม";
                continue;
            }
            $year = substr(preg_replace('/\D/u', '', $yearRaw), 0, 4);

            $level = $this->shortenLevel($levelRaw);
            if ($level === '') {
                $warnings[] = "แถว {$row} กิจกรรม {$actName} (เลขประจำตัว {$code}): ไม่มีระดับชั้น — ข้าม";
                continue;
            }

            if (!preg_match('/(\d+)/u', $semRaw, $sm)) {
                $warnings[] = "แถว {$row} กิจกรรม {$actName} (เลขประจำตัว {$code}): ภาคเรียน \"{$semRaw}\" ต้องมีตัวเลข (1 หรือ 2) — ข้าม";
                continue;
            }
            $sem = $sm[1];

            $hours = is_numeric($hoursRaw) ? (float) $hoursRaw : 0;

            if ($resultRaw === '') {
                $warnings[] = "แถว {$row} กิจกรรม {$actName} (เลขประจำตัว {$code}): ยังไม่มีผลการประเมิน — ข้าม";
                continue;
            }
            if ($resultRaw === 'มผ' || str_contains($resultRaw, 'ไม่ผ่าน')) {
                $grade = 'ไม่ผ่าน';
                $remark = 'ไม่ผ่าน';
            } elseif ($resultRaw === 'ผ' || str_contains($resultRaw, 'ผ่าน')) {
                $grade = 'ผ่าน';
                $remark = 'ผ่าน';
            } else {
                $grade = $resultRaw;
                $remark = $resultRaw;
            }

            $activities[$code][$year]['level'] = $level;
            $activities[$code][$year]['semesters'][$sem][] = [$actName, $hours, $grade, $remark];
        }

        return $activities;
    }
}
