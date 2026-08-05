<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ParsesWideTranscriptSheet;
use App\Console\Commands\Concerns\WritesTranscriptGrades;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\StudentSection;
use App\Models\Personne\Personnel;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportTranscriptFromExcel extends Command
{
    use WritesTranscriptGrades;
    use ParsesWideTranscriptSheet;

    protected $signature = 'import:transcript
        {studentId : student_id ของนักเรียนที่มีอยู่แล้วในระบบ}
        {file? : พาธไฟล์ .xlsx (ไม่ต้องใส่ถ้าใช้ --undo)}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}
        {--undo : ลบข้อมูลที่นำเข้าให้นักเรียนคนนี้ทิ้งทั้งหมด}';

    protected $description = 'นำเข้าเกรดรวม (Transcript) ของนักเรียนคนเดียวจากไฟล์ Excel — '
        . 'ตารางผลการเรียนรายวิชา (สูงสุด 3 ปีการศึกษา/ระดับชั้น เคียงกัน) '
        . 'และตารางกิจกรรมพัฒนาผู้เรียน (แนะแนว/ชุมนุม/ฯลฯ) แยกต่างหากด้านล่าง (ถ้ามี) — '
        . 'นำเข้าทีเดียวหลายคน/ทั้งห้อง ใช้ import:transcript-bulk แทน';

    public function handle(): int
    {
        $student = Student::find($this->argument('studentId'));
        if (!$student) {
            $this->error("ไม่พบนักเรียน (student_id={$this->argument('studentId')}) ในระบบ");
            return self::FAILURE;
        }

        $this->info("นักเรียน: {$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} (รหัส {$student->student_code})");

        $dryRun = (bool) $this->option('dry-run');

        if ($this->option('undo')) {
            return $this->undo($student, $dryRun);
        }

        $path = $this->argument('file');
        if (!$path || !is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        [$data, $activities, $warnings] = $this->parseFile($path);

        if (empty($data) && empty($activities)) {
            $this->error('ไม่พบข้อมูลผลการเรียนในไฟล์ที่อัปโหลด ตรวจสอบว่าใช้แบบฟอร์มที่ถูกต้อง (ต้องมีแถวที่มีคำว่า "ปีการศึกษา")');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
            return self::FAILURE;
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');

        $created = 0;
        $updated = 0;
        $actCreated = 0;
        $actUpdated = 0;

        try {
            DB::transaction(function () use ($student, $data, $activities, &$created, &$updated, &$actCreated, &$actUpdated, &$warnings) {
                $result = $this->writeStudentTranscript($student, $data, $activities, $warnings);
                $created = $result['created'];
                $updated = $result['updated'];
                $actCreated = $result['actCreated'];
                $actUpdated = $result['actUpdated'];

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
        $this->info(($dryRun ? 'จะสร้างผลการเรียนใหม่: ' : 'สร้างผลการเรียนใหม่สำเร็จ: ') . "{$created} รายวิชา");
        if ($updated > 0) {
            $this->info(($dryRun ? 'จะอัปเดตของเดิม: ' : 'อัปเดตของเดิมสำเร็จ: ') . "{$updated} รายวิชา");
        }
        if ($actCreated > 0 || $actUpdated > 0) {
            $this->info(($dryRun ? 'จะบันทึกกิจกรรมพัฒนาผู้เรียนใหม่: ' : 'บันทึกกิจกรรมพัฒนาผู้เรียนใหม่สำเร็จ: ') . "{$actCreated} รายการ");
            if ($actUpdated > 0) {
                $this->info(($dryRun ? 'จะอัปเดตกิจกรรมเดิม: ' : 'อัปเดตกิจกรรมเดิมสำเร็จ: ') . "{$actUpdated} รายการ");
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
        } else {
            $this->newLine();
            $this->comment("เข้าไปดูผลได้ที่หน้าแก้ไขเกรดของนักเรียนคนนี้ หรือใบ ปพ.1: /por1/print/{$student->student_id}");
        }

        return self::SUCCESS;
    }

    // โหลดไฟล์ (มีชีตเดียว) แล้วอ่านด้วย parseTranscriptSheet() ที่ ParsesWideTranscriptSheet ให้มา —
    // ต่างจาก import:transcript-bulk (หลายชีต) ตรงที่ไฟล์เดี่ยวไม่เจอ "ปีการศึกษา" เลยถือเป็น error ทันที
    private function parseFile(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        [$data, $activities, $warnings] = $this->parseTranscriptSheet($sheet);

        if (empty($data) && empty($activities) && empty($warnings)) {
            return [[], [], ['ไม่พบแถวที่มีคำว่า "ปีการศึกษา" ในไฟล์นี้เลย']];
        }

        return [$data, $activities, $warnings];
    }

    /**
     * ลบข้อมูลที่คำสั่งนี้เคยนำเข้าให้นักเรียนคนนี้ทิ้ง — ไล่จาก "ครูนำเข้า" (GRADE-IMPORT) ซึ่งมีแค่คำสั่งนี้เท่านั้นที่ใช้
     * (ครอบคลุมทั้งรายวิชาและกิจกรรมพัฒนาผู้เรียน เพราะใช้ครู/ห้องชุดเดียวกันทั้งหมด)
     * ไม่ลบ subjects/teaching_assigns/class_sections ทิ้ง (อาจมีคนอื่นใช้ร่วม ปล่อยว่างไว้ไม่มีผลกระทบอะไร)
     */
    private function undo(Student $student, bool $dryRun): int
    {
        $teacher = Personnel::where('employee_code', self::IMPORT_TEACHER_CODE)->first();
        if (!$teacher) {
            $this->info('ไม่พบข้อมูลนำเข้าของคำสั่งนี้ในระบบ (ไม่มีอะไรให้ลบ)');
            return self::SUCCESS;
        }

        $assignIds = TeachingAssign::where('personnel_id', $teacher->personnel_id)->pluck('assign_id');
        $sectionIds = TeachingAssign::whereIn('assign_id', $assignIds)->pluck('section_id')->unique();

        $gradeCount = FinalGrade::where('student_id', $student->student_id)->whereIn('assign_id', $assignIds)->count();
        $sectionMemberCount = StudentSection::where('student_id', $student->student_id)->whereIn('section_id', $sectionIds)->count();

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่ลบจริง ===' : '=== กำลังลบข้อมูลจริง ===');
        $this->info(($dryRun ? 'จะลบผลการเรียน/กิจกรรม: ' : 'ลบผลการเรียน/กิจกรรม: ') . "{$gradeCount} รายการ");
        $this->info(($dryRun ? 'จะลบการอยู่ในห้องนำเข้า: ' : 'ลบการอยู่ในห้องนำเข้า: ') . "{$sectionMemberCount} รายการ");

        if (!$dryRun) {
            FinalGrade::where('student_id', $student->student_id)->whereIn('assign_id', $assignIds)->delete();
            StudentSection::where('student_id', $student->student_id)->whereIn('section_id', $sectionIds)->delete();
            $this->comment('ลบเรียบร้อยแล้ว');
        }

        return self::SUCCESS;
    }
}
