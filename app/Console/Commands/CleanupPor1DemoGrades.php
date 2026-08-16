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
 * ล้างห้องปลอมที่เหลือค้างจากคำสั่ง grade:seed-por1-demo ทั้งหมด (section_number=9999
 * "ทดสอบเกรด (ลบได้)") — คำสั่งนั้นมี --undo แต่ลบได้แค่ทีละนักเรียน และตั้งใจไม่ลบตัวห้อง/ครูทดสอบทิ้ง
 * ไว้เผื่อมีคนอื่นใช้ร่วม ทำให้ห้องปลอมค้างโชว์ในรายการห้องเรียนแม้จะล้างเกรดของทุกคนไปหมดแล้ว
 * คำสั่งนี้ลบห้อง 9999 ทั้งหมดพร้อมข้อมูลที่ผูกอยู่ (final_grades/student_sections/teaching_assigns)
 * และครูทดสอบ (GRADE-TEST) ทิ้งไปเลย — ไม่แตะ subjects เพราะอาจเป็นรหัสวิชาจริงที่ใช้ร่วมกับห้องอื่น
 * ไม่แตะห้อง section_number=9998 (นำเข้าเกรดจากไฟล์) เด็ดขาด เพราะเป็นข้อมูลเกรดจริงของนักเรียนจริง
 */
class CleanupPor1DemoGrades extends Command
{
    private const TEST_SECTION_NUMBER = 9999;
    private const TEST_TEACHER_CODE = 'GRADE-TEST';

    protected $signature = 'grade:cleanup-por1-demo {--dry-run : ทดสอบเฉยๆ ไม่ลบจริง}';

    protected $description = 'ลบห้องทดสอบ "ทดสอบเกรด (ลบได้)" (section_number=9999) ทั้งหมดที่เหลือค้างจาก grade:seed-por1-demo';

    public function handle(): int
    {
        $sections = ClassSection::where('section_number', self::TEST_SECTION_NUMBER)->get();

        if ($sections->isEmpty()) {
            $this->info('ไม่พบห้องทดสอบ (section_number=9999) ในระบบ — ไม่มีอะไรให้ลบ');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sectionIds = $sections->pluck('section_id');
        $assignIds = TeachingAssign::whereIn('section_id', $sectionIds)->pluck('assign_id');
        $gradeCount = FinalGrade::whereIn('assign_id', $assignIds)->count();
        $studentSectionCount = StudentSection::whereIn('section_id', $sectionIds)->count();

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่ลบข้อมูลจริง ===' : '=== กำลังลบข้อมูลจริง ===');
        $this->line("พบห้องทดสอบ {$sections->count()} ห้อง (คนละภาคเรียนกัน):");
        foreach ($sections as $s) {
            $this->line("  - section_id={$s->section_id}, semester_id={$s->semester_id}, level_id={$s->level_id}");
        }
        $this->line("จะลบ: ผลการเรียน {$gradeCount} รายการ, การลงทะเบียนเข้าห้อง {$studentSectionCount} รายการ, "
            . "การมอบหมายวิชา {$assignIds->count()} รายการ, ห้องเรียน {$sections->count()} ห้อง");

        if (!$dryRun && !$this->confirm('ยืนยันลบห้องทดสอบทั้งหมดนี้พร้อมข้อมูลที่ผูกอยู่?', true)) {
            $this->info('ยกเลิก ไม่มีอะไรถูกลบ');
            return self::SUCCESS;
        }

        if (!$dryRun) {
            DB::transaction(function () use ($sectionIds, $assignIds) {
                FinalGrade::whereIn('assign_id', $assignIds)->delete();
                StudentSection::whereIn('section_id', $sectionIds)->delete();
                TeachingAssign::whereIn('section_id', $sectionIds)->delete();
                ClassSection::whereIn('section_id', $sectionIds)->delete();

                // ครูทดสอบ (GRADE-TEST) มีไว้ใช้เฉพาะห้องทดสอบชุดนี้เท่านั้น ลบทิ้งได้เลยถ้าไม่มีการมอบหมายวิชาอื่นเหลือ
                $teacher = Personnel::where('employee_code', self::TEST_TEACHER_CODE)->first();
                if ($teacher && !TeachingAssign::where('personnel_id', $teacher->personnel_id)->exists()) {
                    $teacher->delete();
                }
            });
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะลบห้องทดสอบ: ' : 'ลบห้องทดสอบสำเร็จ: ') . "{$sections->count()} ห้อง");

        if ($dryRun) {
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกลบจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        }

        return self::SUCCESS;
    }
}
