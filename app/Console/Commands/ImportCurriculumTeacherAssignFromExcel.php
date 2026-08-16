<?php

namespace App\Console\Commands;

use App\Models\Academic\Curriculum;
use App\Models\Academic\CurriculumSubject;
use App\Models\Academic\Subject;
use App\Models\Personne\Personnel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * นำเข้า "มอบหมายครูผู้สอน" ของแผนการเรียน — เบากว่า import:curriculum-plan มาก เพราะไม่แตะข้อมูลวิชาเลย
 * รับแค่ 3 คอลัมน์ (A=รหัสวิชา B=ชื่อ-นามสกุลครูผู้สอน C=ภาคเรียน) ที่เหลือ (ชื่อวิชา/หน่วยกิต/ชม./ประเภท ฯลฯ)
 * ดึงจากข้อมูลวิชาที่มีอยู่จริงในระบบเสมอ ไม่สร้าง/อัปเดตวิชาใหม่ — วิชาที่ยังไม่มีในระบบ (subjects table)
 * จะถูกข้ามทั้งแถวพร้อม warning ให้ไปเพิ่มวิชานั้นในระบบก่อน
 */
class ImportCurriculumTeacherAssignFromExcel extends Command
{
    protected $signature = 'import:curriculum-assign
        {curriculum_id : รหัสหลักสูตร/แผนที่จะมอบหมายครูผู้สอนเข้าไป}
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'มอบหมายครูผู้สอน+ภาคเรียนให้วิชาที่มีอยู่แล้วในแผนการเรียน จากไฟล์ Excel (รหัสวิชา/ชื่อครู/ภาคเรียน)';

    public function handle(): int
    {
        $curriculumId = $this->argument('curriculum_id');
        $path = $this->argument('file');

        $curriculum = Curriculum::find($curriculumId);
        if (!$curriculum) {
            $this->error("ไม่พบแผนการเรียนรหัส {$curriculumId}");
            return self::FAILURE;
        }
        if (!$path || !is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        [$rows, $warnings] = $this->parseFile($path);
        if (empty($rows)) {
            $this->error('ไม่พบข้อมูลในไฟล์ (แถวต้องมีรหัสวิชาที่คอลัมน์ A อย่างน้อย)');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');

        $allPersonnel = Personnel::all();

        $assigned = 0;
        $created = 0;
        $updated = 0;

        try {
            DB::transaction(function () use ($curriculum, $rows, $allPersonnel, &$warnings, &$assigned, &$created, &$updated) {
                foreach ($rows as $row) {
                    $subject = Subject::where('code', $row['code'])->first();
                    if (!$subject) {
                        $warnings[] = "รหัสวิชา \"{$row['code']}\": ไม่พบวิชานี้ในระบบ — ข้ามทั้งแถว (กรุณาเพิ่มวิชานี้ในระบบก่อน)";
                        continue;
                    }

                    $teacherId = null;
                    if ($row['teacher_name'] !== '') {
                        $matches = $this->findPersonnelByName($row['teacher_name'], $allPersonnel);
                        if (count($matches) === 1) {
                            $teacherId = $matches[0]->personnel_id;
                        } elseif (count($matches) > 1) {
                            $warnings[] = "วิชา {$row['code']}: ชื่อครูผู้สอน \"{$row['teacher_name']}\" ตรงกับบุคลากรมากกว่า 1 คนในระบบ — ไม่ได้มอบหมายครู (กรุณาระบุให้ชัดเจนกว่านี้)";
                        } else {
                            $warnings[] = "วิชา {$row['code']}: ไม่พบครูผู้สอนชื่อ \"{$row['teacher_name']}\" ในระบบ — ไม่ได้มอบหมายครู";
                        }
                    }

                    $cs = CurriculumSubject::where('curriculum_id', $curriculum->curriculum_id)
                        ->where('subject_id', $subject->subject_id)->first();

                    $csData = ['personnel_id' => $teacherId];
                    if ($row['semester_type'] !== null) {
                        $csData['semester_type'] = $row['semester_type'];
                    }

                    if ($cs) {
                        $cs->update($csData);
                        $updated++;
                    } else {
                        // วิชายังไม่อยู่ในแผนนี้ — เพิ่มเข้าแผนให้เลย โดยไม่ตั้งค่า credits/hours_per_year/hours_per_week
                        // เอาไว้ (ปล่อย null) จะได้ดึงค่าเริ่มต้นจากตารางวิชากลาง (subjects) เองตามที่ตั้งใจไว้
                        $csData['curriculum_id'] = $curriculum->curriculum_id;
                        $csData['subject_id'] = $subject->subject_id;
                        $csData['semester_type'] = $csData['semester_type'] ?? 'both';
                        $csData['is_required'] = $subject->subject_type !== 'เพิ่มเติม';
                        $cs = CurriculumSubject::create($csData);
                        $created++;
                    }

                    if ($teacherId) {
                        $cs->teachers()->sync([$teacherId]);
                        $assigned++;
                    }
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
        $this->info(($dryRun ? 'จะเพิ่มวิชาเข้าแผน: ' : 'เพิ่มวิชาเข้าแผนสำเร็จ: ') . "{$created} วิชา (อัปเดตของเดิมในแผน {$updated} วิชา)");
        $this->info(($dryRun ? 'จะมอบหมายครูผู้สอน: ' : 'มอบหมายครูผู้สอนสำเร็จ: ') . "{$assigned} วิชา");

        if (!empty($warnings)) {
            $this->newLine();
            $this->warn('รายการที่ข้าม/หาไม่เจอ:');
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

    private function parseFile(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $rows = [];
        $warnings = [];

        // หาแถวหัวตาราง ("รหัสวิชา" ในคอลัมน์ A) แบบยืดหยุ่นเหมือน import:curriculum-plan
        $firstDataRow = 2;
        for ($r = 1; $r <= min(15, $highestRow); $r++) {
            $cellA = trim((string) ($sheet->getCell("A{$r}")->getValue() ?? ''));
            if ($cellA === 'รหัสวิชา') {
                $firstDataRow = $r + 1;
                break;
            }
        }

        for ($r = $firstDataRow; $r <= $highestRow; $r++) {
            $code = trim((string) ($sheet->getCell("A{$r}")->getValue() ?? ''));
            if ($code === '') {
                continue; // แถวว่าง ข้ามเงียบๆ
            }

            $teacherName = trim((string) ($sheet->getCell("B{$r}")->getValue() ?? ''));
            $semesterRaw = trim((string) ($sheet->getCell("C{$r}")->getValue() ?? ''));
            $semesterType = in_array($semesterRaw, ['1', '2'], true) ? $semesterRaw : null;

            $rows[] = [
                'code' => $code,
                'teacher_name' => $teacherName,
                'semester_type' => $semesterType,
            ];
        }

        return [$rows, $warnings];
    }

    // จับคู่ชื่อจากไฟล์กับบุคลากรในระบบ — เทียบแบบตัดช่องว่างทั้งหมดทิ้ง ลองทั้งแบบมี/ไม่มีคำนำหน้า
    private function findPersonnelByName(string $inputName, \Illuminate\Support\Collection $allPersonnel): array
    {
        $normalize = fn ($s) => preg_replace('/\s+/u', '', trim((string) $s));
        $target = $normalize($inputName);
        if ($target === '') {
            return [];
        }

        return $allPersonnel->filter(function ($p) use ($normalize, $target) {
            $withoutPrefix = $normalize($p->thai_firstname . $p->thai_lastname);
            $withPrefix = $normalize(($p->thai_prefix ?? '') . $p->thai_firstname . $p->thai_lastname);
            return $target === $withoutPrefix || $target === $withPrefix;
        })->values()->all();
    }
}
