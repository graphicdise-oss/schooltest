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
 * ย้ายผลการเรียนที่นำเข้าจากไฟล์ (ติดอยู่ในห้องปลอม "นำเข้าเกรดจากไฟล์" section_number=9998) ไปอยู่ห้อง
 * เรียนจริงของนักเรียนคนนั้นแทน — ใช้ตอนนักเรียนมีห้องเรียนจริงอยู่แล้วในระบบสำหรับปี/เทอมนั้น (เช่น จัด
 * เข้าห้องไว้ก่อน แล้วค่อยนำเข้าเกรดย้อนหลังทีหลัง) ทำให้ผลการเรียนไปติดห้องปลอมทั้งที่มีห้องจริงรออยู่แล้ว
 *
 * ทำทีละนักเรียน/ทีละปี-เทอม: ถ้าเจอห้องจริง (ระดับชั้น+เทอมเดียวกัน ไม่ใช่ห้องปลอม) ที่นักเรียนคนนี้อยู่
 * จะย้ายผลการเรียนทุกวิชา (สร้าง/หา teaching_assigns ในห้องจริงให้ ครู/วิชาเดิม) แล้วลบการลงทะเบียนในห้อง
 * ปลอมทิ้ง ถ้าไม่เจอห้องจริงเลย ปล่อยไว้ที่ห้องปลอมเหมือนเดิม (ไม่มีที่ให้ย้ายไป)
 *
 * หลังรันแล้วห้องปลอมที่ไม่มีข้อมูลเหลือเลย ใช้ grade:cleanup-import-sections ลบทิ้งต่อได้ (คำสั่งนั้นจะ
 * เตือนถ้ายังมีผลการเรียนเหลืออยู่ ป้องกันลบข้อมูลที่ยังไม่ได้ย้าย)
 */
class RelinkImportedGradesToRealSections extends Command
{
    private const IMPORT_SECTION_NUMBER = 9998;

    protected $signature = 'grade:relink-import-sections {--dry-run : ทดสอบเฉยๆ ไม่ย้ายจริง}';

    protected $description = 'ย้ายผลการเรียนที่นำเข้าจากไฟล์ (ห้องปลอม 9998) ไปห้องเรียนจริงของนักเรียน ถ้ามีห้องจริงอยู่แล้ว';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $fakeSections = ClassSection::where('section_number', self::IMPORT_SECTION_NUMBER)->get();

        if ($fakeSections->isEmpty()) {
            $this->info('ไม่พบห้อง "นำเข้าเกรดจากไฟล์" (section_number=9998) ในระบบ — ไม่มีอะไรให้ย้าย');
            return self::SUCCESS;
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่ย้ายข้อมูลจริง ===' : '=== กำลังย้ายข้อมูลจริง ===');

        $movedStudents = 0;
        $movedGrades = 0;
        $skippedNoRealSection = 0;

        try {
            DB::transaction(function () use ($fakeSections, $dryRun, &$movedStudents, &$movedGrades, &$skippedNoRealSection) {
            foreach ($fakeSections as $fake) {
                $studentSections = StudentSection::where('section_id', $fake->section_id)->get();

                foreach ($studentSections as $ss) {
                    // ห้องจริงของนักเรียนคนนี้ ระดับชั้น+เทอมเดียวกับห้องปลอม (ถ้ามี)
                    $realSection = ClassSection::real()
                        ->where('semester_id', $fake->semester_id)
                        ->where('level_id', $fake->level_id)
                        ->whereHas('studentSections', fn ($q) => $q->where('student_id', $ss->student_id))
                        ->first();

                    if (!$realSection) {
                        $skippedNoRealSection++;
                        continue;
                    }

                    $fakeAssigns = TeachingAssign::where('section_id', $fake->section_id)
                        ->where('semester_id', $fake->semester_id)
                        ->get();

                    $studentMoved = false;
                    foreach ($fakeAssigns as $fa) {
                        $grade = FinalGrade::where('student_id', $ss->student_id)
                            ->where('assign_id', $fa->assign_id)
                            ->first();
                        if (!$grade) {
                            continue;
                        }

                        $realAssign = TeachingAssign::firstOrCreate([
                            'personnel_id' => $fa->personnel_id,
                            'subject_id' => $fa->subject_id,
                            'section_id' => $realSection->section_id,
                            'semester_id' => $fa->semester_id,
                        ]);

                        // ห้องจริงมีเกรดวิชานี้อยู่แล้ว (เช่น ครูกรอกเองไปแล้ว) — ไม่ทับ ปล่อยของเดิมในห้องปลอมไว้เฉยๆ
                        $alreadyInReal = FinalGrade::where('student_id', $ss->student_id)
                            ->where('assign_id', $realAssign->assign_id)
                            ->exists();

                        if ($alreadyInReal) {
                            continue;
                        }

                        $this->line("  - {$ss->student_id}: ย้ายวิชา subject_id={$fa->subject_id} จากห้อง {$fake->section_id} ไปห้อง {$realSection->section_id}");
                        if (!$dryRun) {
                            $grade->update(['assign_id' => $realAssign->assign_id]);
                        }
                        $movedGrades++;
                        $studentMoved = true;
                    }

                    if ($studentMoved) {
                        $movedStudents++;
                        if (!$dryRun) {
                            $ss->delete();
                        }
                    }
                }
            }

            if ($dryRun) {
                // dry-run ต้องไม่ทิ้งผลกระทบอะไรไว้เลย (กันเผลอ firstOrCreate สร้าง teaching_assigns ค้าง)
                throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
            }
        });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== '__DRY_RUN_ROLLBACK__') {
                throw $e;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะย้าย: ' : 'ย้ายสำเร็จ: ') . "{$movedGrades} รายวิชา ของนักเรียน {$movedStudents} คน");
        if ($skippedNoRealSection > 0) {
            $this->comment("ข้าม {$skippedNoRealSection} รายการ (ไม่มีห้องเรียนจริงให้ย้ายไป ยังคงอยู่ห้องปลอมเหมือนเดิม)");
        }
        if ($dryRun) {
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกย้ายจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        } else {
            $this->comment('ห้องปลอมที่ไม่มีข้อมูลเหลือแล้ว ลบทิ้งต่อได้ด้วย: php artisan grade:cleanup-import-sections');
        }

        return self::SUCCESS;
    }
}
