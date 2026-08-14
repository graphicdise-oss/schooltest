<?php

namespace App\Console\Commands;

use App\Models\Academic\ClassSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\StudentAssessment;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\StudentSection;
use App\Models\Academic\SubjectAssessment;
use App\Models\Academic\TeachingAssign;
use App\Models\Student;
use App\Models\StudentEducation;
use App\Models\StudentFamily;
use Illuminate\Console\Command;

class SeedTestPorData extends Command
{
    protected $signature = 'test:seed-por-data {student_id=11866} {--section_id=} {--force-grades}';

    protected $description = 'สร้างข้อมูลปลอม (บิดา/มารดา, เลขที่เอกสาร, ผลการเรียน ฯลฯ) ให้นักเรียนคนเดียว เพื่อทดสอบพิมพ์ ปพ.1/2/3/5/6/7 โดยไม่แตะข้อมูลนักเรียนคนอื่น';

    public function handle()
    {
        $studentId = (int) $this->argument('student_id');

        $student = Student::find($studentId);
        if (!$student) {
            $this->error("ไม่พบนักเรียน student_id={$studentId} กรุณาสร้างนักเรียนคนนี้ในระบบก่อน (ผ่านหน้าเพิ่มนักเรียนปกติ) แล้วค่อยรันคำสั่งนี้ใหม่");
            return 1;
        }

        $this->info("พบนักเรียน: {$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} (student_id={$studentId})");

        // เติมข้อมูลพื้นฐานของนักเรียนเฉพาะช่องที่ยังว่าง (ไม่ทับของเดิมถ้ามีอยู่แล้ว)
        $fill = [];
        if (empty($student->id_card_number)) $fill['id_card_number'] = '1' . str_pad((string) $studentId, 12, '0', STR_PAD_LEFT);
        if (empty($student->date_of_birth))  $fill['date_of_birth']  = '2008-05-15';
        if (empty($student->gender))         $fill['gender']         = 'M';
        if (empty($student->nationality))    $fill['nationality']    = 'ไทย';
        if (empty($student->ethnicity))      $fill['ethnicity']      = 'ไทย';
        if ($fill) {
            $student->update($fill);
            $this->line('  - เติมข้อมูลพื้นฐานที่ขาด: ' . implode(', ', array_keys($fill)));
        }

        // บิดา/มารดา (ใช้ใน ปพ.1, ปพ.3, ปพ.7)
        if ($student->families()->count() === 0) {
            $lastname = $student->thai_lastname ?: 'ทดสอบ';
            StudentFamily::create([
                'student_id' => $studentId, 'guardian_type' => 'บิดา', 'family_type' => 'บิดา',
                'prefix_th' => 'นาย', 'first_name_th' => 'ทดสอบ', 'last_name_th' => $lastname,
                'occupation' => 'รับจ้าง', 'phone_mobile' => '0812345678',
            ]);
            StudentFamily::create([
                'student_id' => $studentId, 'guardian_type' => 'มารดา', 'family_type' => 'มารดา',
                'prefix_th' => 'นาง', 'first_name_th' => 'สมมติ', 'last_name_th' => $lastname,
                'occupation' => 'ค้าขาย', 'phone_mobile' => '0898765432',
            ]);
            $this->line('  - สร้างข้อมูลบิดา/มารดา (ปลอม)');
        }

        // ประวัติการศึกษา (ใช้ใน ปพ.1)
        if (!$student->education) {
            StudentEducation::create([
                'student_id' => $studentId,
                'education_level' => 'มัธยมศึกษาปีที่ 6',
            ]);
            $this->line('  - สร้างข้อมูลการศึกษา (ปลอม)');
        }

        // หา/สร้างห้องเรียนที่นักเรียนสังกัดอยู่
        $studentSection = StudentSection::where('student_id', $studentId)->latest('id')->first();

        if (!$studentSection) {
            $sectionOpt = $this->option('section_id');
            $section = $sectionOpt
                ? ClassSection::find($sectionOpt)
                : ClassSection::whereHas('semester', fn($q) => $q->where('is_current', true))->first();

            if (!$section) {
                $this->error('ไม่พบห้องเรียน/ภาคเรียนปัจจุบันในระบบ ใส่ --section_id=... เพื่อระบุห้องเอง หรือสร้างภาคเรียน/ห้องเรียนที่ is_current=1 ก่อน');
                return 1;
            }

            $nextNo = (StudentSection::where('section_id', $section->section_id)->max('student_number') ?? 0) + 1;
            $studentSection = StudentSection::create([
                'student_id' => $studentId,
                'section_id' => $section->section_id,
                'student_number' => $nextNo,
                'status' => 'กำลังศึกษา',
            ]);
            $this->line("  - เพิ่มนักเรียนเข้าห้อง section_id={$section->section_id} (เลขที่ {$nextNo})");
        }

        $section = ClassSection::find($studentSection->section_id);
        $semesterId = $section->semester_id;

        // เลขที่เอกสาร ปพ.2 รายภาคเรียน (ใช้ใน ปพ.3)
        $docNum = StudentDocNumber::where('student_id', $studentId)->where('semester_id', $semesterId)->first();
        if (!$docNum) {
            StudentDocNumber::create([
                'student_id' => $studentId, 'semester_id' => $semesterId, 'level_id' => $section->level_id,
                'doc_set' => '1', 'doc_number' => str_pad((string) $studentId, 5, '0', STR_PAD_LEFT),
            ]);
            $this->line('  - สร้างเลขที่ ปพ.2 รายภาคเรียน (ปลอม)');
        }

        // เอกสาร ปพ.2 ฉบับจริง (ใช้ใน ปพ.2)
        $pp2 = Pp2Document::where('student_id', $studentId)->where('section_id', $section->section_id)->first();
        if (!$pp2) {
            Pp2Document::create([
                'student_id' => $studentId, 'section_id' => $section->section_id,
                'doc_number' => str_pad((string) $studentId, 5, '0', STR_PAD_LEFT),
                'issued_date' => now(),
            ]);
            $this->line('  - สร้างเอกสาร ปพ.2 (ปลอม)');
        }

        // ผลการเรียนทุกวิชาที่สอนในห้องนี้ (ใช้ใน ปพ.1, ปพ.3, ปพ.5, ปพ.6)
        $assigns = TeachingAssign::where('section_id', $section->section_id)->get();
        $gradeCount = 0;
        foreach ($assigns as $assign) {
            $exists = FinalGrade::where('student_id', $studentId)->where('assign_id', $assign->assign_id)->first();
            if ($exists && !$this->option('force-grades')) {
                continue;
            }
            $score = rand(60, 95);
            $calc = FinalGrade::calculateGrade($score);
            $data = [
                'student_id' => $studentId, 'assign_id' => $assign->assign_id, 'semester_id' => $semesterId,
                'total_score' => $score, 'grade' => $calc['grade'], 'gpa_point' => $calc['gpa'],
            ];
            $exists ? $exists->update($data) : FinalGrade::create($data);
            $gradeCount++;

            // ผลประเมินคุณลักษณะรายวิชา (ใช้ใน ปพ.5)
            SubjectAssessment::firstOrCreate(
                ['assign_id' => $assign->assign_id, 'student_id' => $studentId],
                ['desired_char' => 'ดีเยี่ยม', 'reading_thinking' => 'ดี', 'competency' => 'ดีเยี่ยม']
            );
        }
        $this->line("  - สร้าง/อัปเดตผลการเรียน {$gradeCount} รายวิชา (คะแนนสุ่ม 60-95, ปลอม)");
        if ($assigns->isEmpty()) {
            $this->warn('  - ห้องนี้ยังไม่มีรายวิชา/ครูผู้สอน (teaching_assigns) จึงไม่มีผลการเรียนให้สร้าง — ปพ.3/5/6 จะไม่มีข้อมูลวิชา');
        }

        // ผลประเมินคุณลักษณะ/การอ่านคิดวิเคราะห์ภาพรวมรายภาคเรียน (ใช้ใน ปพ.6)
        StudentAssessment::firstOrCreate(
            ['student_id' => $studentId, 'semester_id' => $semesterId],
            ['reading_thinking' => 'ดีเยี่ยม', 'desired_char' => 'ดีเยี่ยม', 'activity' => 'ผ่าน']
        );
        $this->line('  - สร้างผลประเมินคุณลักษณะ/การอ่านคิดวิเคราะห์ภาพรวม (ปลอม)');

        $this->newLine();
        $this->info("เสร็จแล้ว! ทดสอบพิมพ์เอกสารของนักเรียนคนนี้ได้เลย (section_id={$section->section_id}, semester_id={$semesterId})");
        $this->warn("ข้อมูลทั้งหมดที่สร้างเป็นข้อมูลปลอม ก่อนใช้งานจริงให้รัน: php artisan test:unseed-por-data {$studentId}");

        return 0;
    }
}
