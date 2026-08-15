<?php

namespace App\Console\Commands;

use App\Models\Academic\FinalGrade;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\StudentAssessment;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\SubjectAssessment;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\TimetableSlot;
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
        $input = trim((string) $this->argument('student_id'));

        // รับได้ทั้ง student_id (primary key ในระบบ) และ student_code (รหัสนักเรียนที่เห็นในตาราง)
        $student = Student::find($input) ?? Student::where('student_code', $input)->first();
        if (!$student) {
            $this->error("ไม่พบนักเรียนที่ student_id หรือ รหัสนักเรียน (student_code) = {$input}");
            return 1;
        }

        $studentId = $student->student_id;

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

        // แก้ผลข้างเคียงจากบั๊กเดิม: seed เคยเผลอเติมตารางสอนปลอมให้ห้อง "นำเข้าเกรดจากไฟล์ (ไม่ใช่ห้องเรียนจริง)"
        // (section_number 9998) ถ้าห้องนี้ของนักเรียนคนนี้ดันเป็นห้องล่าสุดตอนรัน seed — ห้องนี้ไม่มีตารางสอนจริง
        // เลยต้องลบตารางสอนที่ติดมาออกเสมอ ไม่ผูกกับ student คนใดคนหนึ่ง เพราะห้องปลอมนี้ใช้ร่วมกันทุกคน
        $fakeAssignIds = TeachingAssign::whereHas('classSection', fn($q) => $q->where('section_number', 9998))
            ->pluck('assign_id');
        $fakeSlotCount = TimetableSlot::whereIn('assign_id', $fakeAssignIds)->delete();
        if ($fakeSlotCount) {
            $this->line("  - ลบตารางสอนปลอมที่ติดห้อง 'นำเข้าเกรดจากไฟล์ (ไม่ใช่ห้องเรียนจริง)' {$fakeSlotCount} รายการ (ไม่ใช่ห้องเรียนจริง ไม่ควรมีตารางสอน)");
        }

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
