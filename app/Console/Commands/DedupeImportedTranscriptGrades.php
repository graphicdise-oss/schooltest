<?php

namespace App\Console\Commands;

use App\Models\Academic\FinalGrade;
use App\Models\Academic\TeachingAssign;
use App\Models\Personne\Personnel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ลบผลการเรียนที่นำเข้าจากไฟล์ (import:transcript / import:transcript-bulk) ที่ซ้ำกัน — เกิดจากนำเข้า
 * ไฟล์เดียวกันซ้ำตอนยังไม่รู้ห้องจริง (ตกห้องปลอม 9998) แล้วนำเข้าใหม่ตอนรู้ห้องจริงแล้ว หรือ
 * grade:relink-import-sections รุ่นเก่าเจอเกรดในห้องจริงอยู่ก่อนแล้วเลยข้าม ทิ้งของเดิมในห้องปลอม
 * ค้างไว้คู่กัน ทำให้หน้า ปพ.1 แสดงวิชาเดียวกันซ้ำ 2 แถวในเทอมเดียวกัน (ตอนนี้ทั้งสองจุดแก้ไม่ให้เกิดซ้ำ
 * อีกแล้ว คำสั่งนี้ไว้ล้างของเก่าที่ซ้ำอยู่ก่อนหน้าเท่านั้น)
 *
 * กวาดเฉพาะผลการเรียนที่ teaching_assign ผูกกับครู "นำเข้าเกรด (ระบบ)" (GRADE-IMPORT) เท่านั้น (ปลอดภัย
 * ไม่แตะเกรดที่ครูจริงกรอกเอง) จัดกลุ่มตาม นักเรียน+เทอม+วิชา แล้วเก็บไว้แถวเดียว (เลือกห้องจริงก่อนห้อง
 * ปลอม ถ้าเลือกไม่ได้ เลือกแถวที่อัปเดตล่าสุด) ลบส่วนเกินทิ้ง
 */
class DedupeImportedTranscriptGrades extends Command
{
    private const IMPORT_TEACHER_CODE = 'GRADE-IMPORT';
    private const IMPORT_SECTION_NUMBER = 9998;

    protected $signature = 'grade:dedupe-import {--dry-run : ทดสอบเฉยๆ ไม่ลบจริง}';

    protected $description = 'ลบผลการเรียนที่นำเข้าจากไฟล์ที่ซ้ำกัน (วิชาเดียวกัน เทอมเดียวกัน นักเรียนคนเดียวกัน) เหลือไว้แถวเดียว';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $teacher = Personnel::where('employee_code', self::IMPORT_TEACHER_CODE)->first();
        if (!$teacher) {
            $this->info('ไม่พบครู "นำเข้าเกรด (ระบบ)" — ยังไม่เคยนำเข้าเกรดจากไฟล์เลย ไม่มีอะไรให้ลบซ้ำ');
            return self::SUCCESS;
        }

        $assignIds = TeachingAssign::where('personnel_id', $teacher->personnel_id)->pluck('assign_id');
        if ($assignIds->isEmpty()) {
            $this->info('ไม่มีการมอบหมายวิชาของครูนำเข้าเกรดเลย — ไม่มีอะไรให้ลบซ้ำ');
            return self::SUCCESS;
        }

        $grades = FinalGrade::with(['teachingAssign.classSection'])
            ->whereIn('assign_id', $assignIds)
            ->get();

        $groups = $grades->groupBy(fn ($g) => $g->student_id . '|' . $g->semester_id . '|' . $g->teachingAssign->subject_id);
        $dupGroups = $groups->filter(fn ($g) => $g->count() > 1);

        if ($dupGroups->isEmpty()) {
            $this->info('ไม่พบผลการเรียนที่นำเข้าซ้ำกันเลย');
            return self::SUCCESS;
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่ลบข้อมูลจริง ===' : '=== กำลังลบข้อมูลซ้ำจริง ===');
        $this->line("พบ {$dupGroups->count()} กลุ่มที่ซ้ำกัน (นักเรียน+เทอม+วิชา)");

        $studentsAffected = [];
        $deletedCount = 0;

        $run = function () use ($dupGroups, $dryRun, &$studentsAffected, &$deletedCount) {
            foreach ($dupGroups as $key => $rows) {
                // เก็บแถวที่อยู่ห้องจริงก่อน (ไม่ใช่ห้องปลอม 9998) ถ้าเสมอกัน (ทั้งคู่จริง/ทั้งคู่ปลอม)
                // ให้เก็บแถวที่อัปเดตล่าสุด
                $sorted = $rows->sort(function ($a, $b) {
                    $aReal = $a->teachingAssign->classSection
                        && (string) $a->teachingAssign->classSection->section_number !== (string) self::IMPORT_SECTION_NUMBER;
                    $bReal = $b->teachingAssign->classSection
                        && (string) $b->teachingAssign->classSection->section_number !== (string) self::IMPORT_SECTION_NUMBER;
                    if ($aReal !== $bReal) {
                        return $aReal ? -1 : 1;
                    }
                    return strtotime($b->updated_at) <=> strtotime($a->updated_at);
                })->values();

                $keep = $sorted->first();
                $remove = $sorted->slice(1);

                [$studentId, $semesterId, $subjectId] = explode('|', $key);
                $this->line("  - student_id={$studentId} semester_id={$semesterId} subject_id={$subjectId}: "
                    . "เก็บ grade_id={$keep->grade_id} (section_id={$keep->teachingAssign->section_id}), "
                    . "ลบ " . $remove->pluck('grade_id')->implode(', '));

                $studentsAffected[$studentId] = true;

                if (!$dryRun) {
                    FinalGrade::whereIn('grade_id', $remove->pluck('grade_id'))->delete();
                }
                $deletedCount += $remove->count();
            }
        };

        if ($dryRun) {
            $run();
        } else {
            DB::transaction($run);
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะลบ: ' : 'ลบสำเร็จ: ') . "{$deletedCount} รายการซ้ำ ของนักเรียน " . count($studentsAffected) . ' คน');
        if ($dryRun) {
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกลบจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        }

        return self::SUCCESS;
    }
}
