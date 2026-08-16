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
 * นำเข้ารายวิชาของแผนการเรียน (หลักสูตร) จากไฟล์ Excel รูปแบบ "PlanCourses" — 1 แถว = 1 วิชา
 * คอลัมน์คงที่ตามตำแหน่ง (A-M) เรียงลำดับให้ตรงกับตาราง "จัดการวิชาเรียน" ในหน้าเว็บ:
 *   A=รหัสวิชา B=หน่วยกิต C=ชื่อวิชา D-H=ชื่อ-นามสกุลครูผู้สอน (สูงสุด 5 คน) I=ภาคเรียน(1/2)
 *   J=ชม./ปี K=ชม./สัปดาห์ L=ประเภทรายวิชา(รายวิชาพื้นฐาน/เพิ่มเติม/กิจกรรมพัฒนาผู้เรียน) M=กลุ่มสาระการเรียนรู้
 * วิชาที่ยังไม่มีในระบบจะถูกสร้างใหม่ (จับคู่ด้วยรหัสวิชา) ส่วนที่มีอยู่แล้วจะอัปเดตข้อมูลให้ตรงกับไฟล์
 * ครูผู้สอนจับคู่ด้วยชื่อ-นามสกุล (เทียบแบบตัดช่องว่าง/คำนำหน้าทิ้ง จะใส่คำนำหน้าหรือไม่ใส่ก็ได้)
 * คนไหนหาไม่เจอหรือชื่อซ้ำกันหลายคน ข้ามเฉพาะคนนั้น ไม่ข้ามทั้งแถว
 */
class ImportCurriculumPlanFromExcel extends Command
{
    private const SUBJECT_TYPE_MAP = [
        'รายวิชาพื้นฐาน' => 'พื้นฐาน',
        'รายวิชาเพิ่มเติม' => 'เพิ่มเติม',
        'กิจกรรมพัฒนาผู้เรียน' => 'กิจกรรม',
    ];

    private const TEACHER_COLUMNS = ['D', 'E', 'F', 'G', 'H'];

    protected $signature = 'import:curriculum-plan
        {curriculum_id : รหัสหลักสูตร/แผนที่จะนำเข้าวิชาเข้าไป}
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'นำเข้ารายวิชา (พร้อมครูผู้สอนหลายคน) ของแผนการเรียนจากไฟล์ Excel รูปแบบ PlanCourses';

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
            $this->error('ไม่พบข้อมูลวิชาในไฟล์ (แถวต้องมีรหัสวิชาที่คอลัมน์ A อย่างน้อย)');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');

        // ดึงบุคลากรทั้งหมดมาไว้ล่วงหน้าครั้งเดียว แล้วจับคู่ชื่อในหน่วยความจำ (เร็วกว่า query ทีละแถว)
        $allPersonnel = Personnel::all();

        $subjectCreated = 0;
        $subjectUpdated = 0;
        $planCreated = 0;
        $planUpdated = 0;
        $teacherLinks = 0;

        try {
            DB::transaction(function () use ($curriculum, $rows, &$warnings, &$subjectCreated, &$subjectUpdated, &$planCreated, &$planUpdated, &$teacherLinks) {
                foreach ($rows as $row) {
                    $subject = Subject::where('code', $row['code'])->first();
                    $subjectData = [
                        'name_th'        => $row['name'],
                        'subject_group'  => $row['group'],
                        'subject_type'   => $row['type'],
                        'credits'        => $row['credits'],
                        'hours_per_week' => $row['hours_per_week'],
                        'hours_per_year' => $row['hours_per_year'],
                        'is_active'      => true,
                    ];
                    if ($subject) {
                        $subject->update($subjectData);
                        $subjectUpdated++;
                    } else {
                        $subject = Subject::create(['code' => $row['code']] + $subjectData);
                        $subjectCreated++;
                    }

                    // จับคู่ครูผู้สอนด้วยชื่อ-นามสกุล — หาไม่เจอ/ชื่อซ้ำกันหลายคน ข้ามเฉพาะคนนั้น (ไม่ error ทั้งแถว)
                    $teacherIds = [];
                    foreach ($row['teacher_names'] as $teacherName) {
                        $matches = $this->findPersonnelByName($teacherName, $allPersonnel);
                        if (count($matches) === 1) {
                            $teacherIds[] = $matches[0]->personnel_id;
                        } elseif (count($matches) > 1) {
                            $warnings[] = "วิชา {$row['code']}: ชื่อครูผู้สอน \"{$teacherName}\" ตรงกับบุคลากรมากกว่า 1 คนในระบบ — ข้ามคนนี้ (กรุณาระบุให้ชัดเจนกว่านี้)";
                        } else {
                            $warnings[] = "วิชา {$row['code']}: ไม่พบครูผู้สอนชื่อ \"{$teacherName}\" ในระบบ — ข้ามคนนี้";
                        }
                    }

                    $isRequired = $row['type'] !== 'เพิ่มเติม';
                    $cs = CurriculumSubject::where('curriculum_id', $curriculum->curriculum_id)
                        ->where('subject_id', $subject->subject_id)->first();
                    $csData = [
                        'semester_type'  => $row['semester_type'],
                        'is_required'    => $isRequired,
                        'personnel_id'   => $teacherIds[0] ?? null,
                        'credits'        => $row['credits'],
                        'hours_per_year' => $row['hours_per_year'],
                        'hours_per_week' => $row['hours_per_week'],
                    ];
                    if ($cs) {
                        $cs->update($csData);
                        $planUpdated++;
                    } else {
                        $cs = CurriculumSubject::create(['curriculum_id' => $curriculum->curriculum_id, 'subject_id' => $subject->subject_id] + $csData);
                        $planCreated++;
                    }

                    $cs->teachers()->sync($teacherIds);
                    $teacherLinks += count($teacherIds);
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
        $this->info(($dryRun ? 'จะสร้างวิชาใหม่ในระบบ: ' : 'สร้างวิชาใหม่ในระบบสำเร็จ: ') . "{$subjectCreated} วิชา (อัปเดตของเดิม {$subjectUpdated} วิชา)");
        $this->info(($dryRun ? 'จะเพิ่มวิชาเข้าแผน: ' : 'เพิ่มวิชาเข้าแผนสำเร็จ: ') . "{$planCreated} วิชา (อัปเดตของเดิมในแผน {$planUpdated} วิชา)");
        $this->info(($dryRun ? 'จะเชื่อมครูผู้สอน: ' : 'เชื่อมครูผู้สอนสำเร็จ: ') . "{$teacherLinks} รายการ (วิชา-ครู)");

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

        // หาแถวข้อมูลเริ่มต้นแบบยืดหยุ่น แทนการอิงเลขแถวตายตัว เพราะไฟล์ที่นำเข้าได้มีสองรูปแบบ:
        // 1) ไฟล์ "PlanCourses" ต้นฉบับจากภายนอก — หัวตารางอยู่แถว 1 ข้อมูลเริ่มแถว 2
        // 2) แบบฟอร์มที่ดาวน์โหลดจากปุ่ม "นำเข้าจาก Excel" ในระบบ (CurriculumController::downloadTemplate())
        //    ซึ่งมีหัวโรงเรียน+คำแนะนำนำหน้า หัวตารางเลยไปอยู่แถว 6 ข้อมูลเริ่มแถว 7
        // จึงค้นหาแถวที่คอลัมน์ A มีคำว่า "รหัสวิชา" (หัวตารางจริง ไม่ใช่ข้อมูลวิชา) แล้วเริ่มอ่านข้อมูลถัดจากแถวนั้น
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

            // subjects.credits เป็น NOT NULL ในฐานข้อมูลจริง (แถวกิจกรรมพัฒนาผู้เรียนบางแถวในไฟล์ไม่กรอกหน่วยกิตไว้เลย) จึงต้อง default เป็น 0 แทน null
            $credits = $this->numOrNull($sheet->getCell("B{$r}")->getValue()) ?? 0;
            $name = trim((string) ($sheet->getCell("C{$r}")->getValue() ?? ''));
            $semester = trim((string) ($sheet->getCell("I{$r}")->getValue() ?? ''));
            $hoursPerYear = $this->numOrNull($sheet->getCell("J{$r}")->getValue());
            $hoursPerWeek = $this->numOrNull($sheet->getCell("K{$r}")->getValue());
            $typeRaw = trim((string) ($sheet->getCell("L{$r}")->getValue() ?? ''));
            $group = trim((string) ($sheet->getCell("M{$r}")->getValue() ?? ''));

            if ($name === '') {
                $warnings[] = "แถว {$r} (รหัสวิชา {$code}): ไม่มีชื่อวิชา — ข้ามทั้งแถว";
                continue;
            }

            $type = self::SUBJECT_TYPE_MAP[$typeRaw] ?? ($typeRaw !== '' ? $typeRaw : 'พื้นฐาน');
            $semesterType = in_array($semester, ['1', '2'], true) ? $semester : 'both';

            $teacherNames = [];
            foreach (self::TEACHER_COLUMNS as $col) {
                $v = trim((string) ($sheet->getCell("{$col}{$r}")->getValue() ?? ''));
                if ($v !== '') {
                    $teacherNames[] = $v;
                }
            }

            $rows[] = [
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'group' => $group,
                'credits' => $credits,
                'semester_type' => $semesterType,
                'hours_per_week' => $hoursPerWeek,
                'hours_per_year' => $hoursPerYear,
                'teacher_names' => $teacherNames,
            ];
        }

        return [$rows, $warnings];
    }

    private function numOrNull($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    // จับคู่ชื่อจากไฟล์กับบุคลากรในระบบ — เทียบแบบตัดช่องว่างทั้งหมดทิ้ง ลองทั้งแบบมี/ไม่มีคำนำหน้า
    // เผื่อผู้กรอกพิมพ์ "นายสมชาย ใจดี", "สมชาย ใจดี" หรือเว้นวรรคไม่ตรงกันก็ยังจับคู่ได้
    // คืนค่าเป็น array เผื่อกรณีชื่อซ้ำกันหลายคน (ผู้เรียกจะตัดสินใจเองว่าจะข้ามหรือไม่)
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
