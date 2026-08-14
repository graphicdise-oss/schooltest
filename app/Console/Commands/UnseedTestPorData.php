<?php

namespace App\Console\Commands;

use App\Models\Academic\FinalGrade;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\StudentAssessment;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\SubjectAssessment;
use App\Models\Student;
use App\Models\StudentEducation;
use App\Models\StudentFamily;
use Illuminate\Console\Command;

class UnseedTestPorData extends Command
{
    protected $signature = 'test:unseed-por-data {student_id=11866} {--with-section : ลบการสังกัดห้องเรียน (student_sections) ของนักเรียนคนนี้ด้วย}';

    protected $description = 'ลบข้อมูลปลอมที่สร้างด้วย test:seed-por-data ออก (บิดา/มารดา, เลขที่เอกสาร, ผลการเรียน ฯลฯ) ก่อนใช้งานจริง';

    public function handle()
    {
        $studentId = (int) $this->argument('student_id');

        $student = Student::find($studentId);
        if (!$student) {
            $this->error("ไม่พบนักเรียน student_id={$studentId}");
            return 1;
        }

        if (!$this->confirm("จะลบข้อมูลปลอม (บิดา/มารดา, ผลการเรียน, เลขที่เอกสาร ฯลฯ) ของ {$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} (student_id={$studentId}) ใช่ไหม?", true)) {
            $this->info('ยกเลิก');
            return 0;
        }

        $familyCount = StudentFamily::where('student_id', $studentId)->delete();
        $this->line("  - ลบข้อมูลบิดา/มารดา {$familyCount} รายการ");

        $eduCount = StudentEducation::where('student_id', $studentId)->delete();
        $this->line("  - ลบข้อมูลการศึกษา {$eduCount} รายการ");

        $assignIds = FinalGrade::where('student_id', $studentId)->pluck('assign_id');
        $gradeCount = FinalGrade::where('student_id', $studentId)->delete();
        $this->line("  - ลบผลการเรียน {$gradeCount} รายการ");

        $subjAssessCount = SubjectAssessment::where('student_id', $studentId)
            ->whereIn('assign_id', $assignIds)
            ->delete();
        $this->line("  - ลบผลประเมินคุณลักษณะรายวิชา {$subjAssessCount} รายการ");

        $assessCount = StudentAssessment::where('student_id', $studentId)->delete();
        $this->line("  - ลบผลประเมินคุณลักษณะภาพรวม {$assessCount} รายการ");

        $docNumCount = StudentDocNumber::where('student_id', $studentId)->delete();
        $this->line("  - ลบเลขที่เอกสาร ปพ.2 {$docNumCount} รายการ");

        $pp2Count = Pp2Document::where('student_id', $studentId)->delete();
        $this->line("  - ลบเอกสาร ปพ.2 {$pp2Count} รายการ");

        if ($this->option('with-section')) {
            $ssCount = \App\Models\Academic\StudentSection::where('student_id', $studentId)->delete();
            $this->line("  - ลบการสังกัดห้องเรียน {$ssCount} รายการ");
        } else {
            $this->comment('  - ไม่ได้ลบการสังกัดห้องเรียน (student_sections) — ใส่ --with-section ถ้าต้องการลบด้วย');
        }

        $this->newLine();
        $this->info('ลบข้อมูลปลอมเรียบร้อย พร้อมกรอกข้อมูลจริงต่อได้เลย');

        return 0;
    }
}
