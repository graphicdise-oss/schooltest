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
 * คอลัมน์คงที่ตามตำแหน่ง (A-S) เพราะหัวตารางในไฟล์ต้นทางบางคอลัมน์ติดป้ายผิดสลับกัน (เช่น C ป้ายบอกว่า
 * "กลุ่มสาระ" แต่ค่าจริงเป็นประเภทรายวิชา และ D สลับกัน) จึงอิงตำแหน่งคอลัมน์แทนข้อความหัวตาราง:
 *   A=รหัสวิชา B=ชื่อวิชา C=ประเภทรายวิชา(รายวิชาพื้นฐาน/เพิ่มเติม/กิจกรรมพัฒนาผู้เรียน) D=กลุ่มสาระการเรียนรู้
 *   E=หน่วยกิต F=ภาคเรียน(1/2) G=ชม./สัปดาห์ H=ชม./ปี O-S=เลขบัตรประชาชนครูผู้สอน (สูงสุด 5 คน)
 * วิชาที่ยังไม่มีในระบบจะถูกสร้างใหม่ (จับคู่ด้วยรหัสวิชา) ส่วนที่มีอยู่แล้วจะอัปเดตข้อมูลให้ตรงกับไฟล์
 * ครูผู้สอนจับคู่ด้วยเลขบัตรประชาชน (personnels.id_card_number) — คนไหนหาไม่เจอ ข้ามเฉพาะคนนั้น ไม่ข้ามทั้งแถว
 */
class ImportCurriculumPlanFromExcel extends Command
{
    private const SUBJECT_TYPE_MAP = [
        'รายวิชาพื้นฐาน' => 'พื้นฐาน',
        'รายวิชาเพิ่มเติม' => 'เพิ่มเติม',
        'กิจกรรมพัฒนาผู้เรียน' => 'กิจกรรม',
    ];

    private const TEACHER_COLUMNS = ['O', 'P', 'Q', 'R', 'S'];

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

                    // จับคู่ครูผู้สอนด้วยเลขบัตรประชาชน — หาไม่เจอคนไหน ข้ามเฉพาะคนนั้น (ไม่ error ทั้งแถว)
                    $teacherIds = [];
                    foreach ($row['teacher_id_cards'] as $idCard) {
                        $teacher = Personnel::where('id_card_number', $idCard)->first();
                        if ($teacher) {
                            $teacherIds[] = $teacher->personnel_id;
                        } else {
                            $warnings[] = "วิชา {$row['code']}: ไม่พบครูผู้สอนเลขบัตรประชาชน \"{$idCard}\" ในระบบ — ข้ามคนนี้";
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

        for ($r = 2; $r <= $highestRow; $r++) {
            $code = trim((string) ($sheet->getCell("A{$r}")->getValue() ?? ''));
            if ($code === '') {
                continue; // แถวว่าง ข้ามเงียบๆ
            }

            $name = trim((string) ($sheet->getCell("B{$r}")->getValue() ?? ''));
            $typeRaw = trim((string) ($sheet->getCell("C{$r}")->getValue() ?? ''));
            $group = trim((string) ($sheet->getCell("D{$r}")->getValue() ?? ''));
            $credits = $this->numOrNull($sheet->getCell("E{$r}")->getValue());
            $semester = trim((string) ($sheet->getCell("F{$r}")->getValue() ?? ''));
            $hoursPerWeek = $this->numOrNull($sheet->getCell("G{$r}")->getValue());
            $hoursPerYear = $this->numOrNull($sheet->getCell("H{$r}")->getValue());

            if ($name === '') {
                $warnings[] = "แถว {$r} (รหัสวิชา {$code}): ไม่มีชื่อวิชา — ข้ามทั้งแถว";
                continue;
            }

            $type = self::SUBJECT_TYPE_MAP[$typeRaw] ?? ($typeRaw !== '' ? $typeRaw : 'พื้นฐาน');
            $semesterType = in_array($semester, ['1', '2'], true) ? $semester : 'both';

            $teacherIdCards = [];
            foreach (self::TEACHER_COLUMNS as $col) {
                $v = trim((string) ($sheet->getCell("{$col}{$r}")->getValue() ?? ''));
                if ($v !== '') {
                    $teacherIdCards[] = $v;
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
                'teacher_id_cards' => $teacherIdCards,
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
}
