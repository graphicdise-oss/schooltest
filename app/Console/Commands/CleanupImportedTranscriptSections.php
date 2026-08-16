<?php

namespace App\Console\Commands;

use App\Models\Academic\ClassSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\StudentSection;
use App\Models\Academic\TeachingAssign;
use App\Models\Personne\Personnel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ลบห้องปลอม "นำเข้าเกรดจากไฟล์ (ไม่ใช่ห้องเรียนจริง)" (section_number=9998) ที่ import:transcript /
 * import:transcript-bulk สร้างไว้เก็บผลการเรียนที่นำเข้าจากไฟล์ ทิ้งทั้งหมด — คำสั่งนี้ทำลายผลการเรียน
 * ที่นำเข้าไว้จริงของนักเรียนจริงถาวร (ไม่ใช่ข้อมูลทดสอบเหมือน grade:cleanup-por1-demo) ผู้ใช้ต้องยืนยัน
 * ก่อนทุกครั้งว่ายอมรับผลนี้ — ถ้านักเรียนคนไหนยังต้องใช้ผลการเรียนที่นำเข้าไว้ (เช่นพิมพ์ ปพ.1) ห้ามรันคำสั่งนี้
 * ไม่แตะ subjects เพราะรหัสวิชาอาจถูกใช้ร่วมกับหลักสูตร/ห้องเรียนจริงอื่นอยู่
 */
class CleanupImportedTranscriptSections extends Command
{
    private const IMPORT_SECTION_NUMBER = 9998;
    private const IMPORT_TEACHER_CODE = 'GRADE-IMPORT';

    protected $signature = 'grade:cleanup-import-sections {--dry-run : ทดสอบเฉยๆ ไม่ลบจริง}';

    protected $description = 'ลบห้อง "นำเข้าเกรดจากไฟล์" (section_number=9998) ทั้งหมด พร้อมผลการเรียนที่นำเข้าไว้ทั้งหมด (ลบถาวร ทำลายข้อมูลจริง)';

    public function handle(): int
    {
        $sections = ClassSection::where('section_number', self::IMPORT_SECTION_NUMBER)->get();

        if ($sections->isEmpty()) {
            $this->info('ไม่พบห้อง "นำเข้าเกรดจากไฟล์" (section_number=9998) ในระบบ — ไม่มีอะไรให้ลบ');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sectionIds = $sections->pluck('section_id');
        $assignIds = TeachingAssign::whereIn('section_id', $sectionIds)->pluck('assign_id');
        $grades = FinalGrade::whereIn('assign_id', $assignIds)->get();
        $studentCount = $grades->pluck('student_id')->unique()->count();
        $studentSectionCount = StudentSection::whereIn('section_id', $sectionIds)->count();

        $this->warn('*** คำสั่งนี้ลบผลการเรียนจริงที่นำเข้าจากไฟล์ของนักเรียนจริงถาวร ไม่ใช่ข้อมูลทดสอบ ***');
        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่ลบข้อมูลจริง ===' : '=== กำลังลบข้อมูลจริง ===');
        $this->line("พบห้อง 9998 ทั้งหมด {$sections->count()} ห้อง (คนละระดับชั้น/ภาคเรียนกัน):");
        foreach ($sections as $s) {
            $this->line("  - section_id={$s->section_id}, semester_id={$s->semester_id}, level_id={$s->level_id}");
        }
        $this->line("จะลบ: ผลการเรียน {$grades->count()} รายการ ของนักเรียน {$studentCount} คน, "
            . "การลงทะเบียนเข้าห้อง {$studentSectionCount} รายการ, การมอบหมายวิชา {$assignIds->count()} รายการ, "
            . "ห้องเรียน {$sections->count()} ห้อง");

        if (!$dryRun && !$this->confirm('ยืนยันลบผลการเรียนที่นำเข้าไว้ทั้งหมดนี้ถาวร? (กู้คืนไม่ได้ ต้องนำเข้าไฟล์ใหม่ถ้าต้องการอีกครั้ง)', false)) {
            $this->info('ยกเลิก ไม่มีอะไรถูกลบ');
            return self::SUCCESS;
        }

        if (!$dryRun) {
            DB::transaction(function () use ($sectionIds, $assignIds) {
                FinalGrade::whereIn('assign_id', $assignIds)->delete();
                StudentSection::whereIn('section_id', $sectionIds)->delete();
                TeachingAssign::whereIn('section_id', $sectionIds)->delete();
                ClassSection::whereIn('section_id', $sectionIds)->delete();

                // ครูนำเข้าเกรด (GRADE-IMPORT) มีไว้ใช้เฉพาะห้องนำเข้าชุดนี้เท่านั้น ลบทิ้งได้เลยถ้าไม่มีการมอบหมายวิชาอื่นเหลือ
                $teacher = Personnel::where('employee_code', self::IMPORT_TEACHER_CODE)->first();
                if ($teacher && !TeachingAssign::where('personnel_id', $teacher->personnel_id)->exists()) {
                    $teacher->delete();
                }
            });
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะลบห้องนำเข้าเกรด: ' : 'ลบห้องนำเข้าเกรดสำเร็จ: ') . "{$sections->count()} ห้อง ({$grades->count()} ผลการเรียน)");

        if ($dryRun) {
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกลบจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        }

        return self::SUCCESS;
    }
}
