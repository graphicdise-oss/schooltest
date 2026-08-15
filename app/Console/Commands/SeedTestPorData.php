<?php

namespace App\Console\Commands;

use App\Models\Academic\ClassSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Pp2Document;
use App\Models\Academic\StudentAssessment;
use App\Models\Academic\StudentDocNumber;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Semester;
use App\Models\Academic\Subject;
use App\Models\Academic\SubjectAssessment;
use App\Models\Academic\TeachingAssign;
use App\Models\Academic\TimetableSlot;
use App\Models\Personne\Personnel;
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
        $input = trim((string) $this->argument('student_id'));

        // รับได้ทั้ง student_id (primary key ในระบบ) และ student_code (รหัสนักเรียนที่เห็นในตาราง)
        $student = Student::find($input) ?? Student::where('student_code', $input)->first();
        if (!$student) {
            $this->error("ไม่พบนักเรียนที่ student_id หรือ รหัสนักเรียน (student_code) = {$input} กรุณาตรวจสอบรหัสนักเรียนในหน้ารายชื่ออีกครั้ง แล้วค่อยรันคำสั่งนี้ใหม่");
            return 1;
        }

        $studentId = $student->student_id;
        $this->info("พบนักเรียน: {$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} (student_id={$studentId}, รหัสนักเรียน={$student->student_code})");

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

        // วันเริ่ม-สิ้นสุดภาคเรียน (ใช้ใน ปพ.5 หน้าบันทึกเวลาเรียน — ถ้าไม่มีวันที่ ระบบจะไม่สร้างตารางเวลาเรียนให้เลย)
        $semester = Semester::find($semesterId);
        if ($semester && (!$semester->start_date || !$semester->end_date)) {
            $gregYear = ((int) ($semester->academicYear->year_name ?? now()->year + 543)) - 543;
            if ($semester->semester_name == '1') {
                $start = "{$gregYear}-05-16";
                $end   = "{$gregYear}-10-10";
            } else {
                $start = "{$gregYear}-11-01";
                $end   = ($gregYear + 1) . "-03-31";
            }
            $semester->update(['start_date' => $start, 'end_date' => $end]);
            $this->line("  - เติมวันเริ่ม-สิ้นสุดภาคเรียน (ปลอม): {$start} ถึง {$end}");
        }

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

        // ถ้าห้องนี้ยังไม่มีวิชา/ครูผู้สอนเลย (teaching_assigns ว่าง) ให้สร้างให้ก่อน
        // เพราะ ปพ.1/3/5/6 ต้องมีผลการเรียนถึงจะมีข้อมูลให้ทดสอบ
        if (TeachingAssign::where('section_id', $section->section_id)->count() === 0) {
            $teacher = Personnel::where('status', 'ปฏิบัติงาน')->first() ?? Personnel::first();
            if (!$teacher) {
                $teacher = Personnel::create([
                    'thai_prefix' => 'นาย', 'thai_firstname' => 'ครูทดสอบ', 'thai_lastname' => 'ระบบ',
                    'position' => 'ครู', 'status' => 'ปฏิบัติงาน', 'gender' => 'M',
                ]);
                $this->line('  - สร้างครูปลอม 1 คน (ไม่มีบุคลากรในระบบเลย)');
            }

            $subjects = Subject::inRandomOrder()->limit(8)->get();
            if ($subjects->isEmpty()) {
                $fakeSubjects = [
                    ['code' => 'ท21101', 'name_th' => 'ภาษาไทย',            'subject_group' => 'ภาษาไทย',            'subject_type' => 'พื้นฐาน', 'credits' => 1.5],
                    ['code' => 'ค21101', 'name_th' => 'คณิตศาสตร์',          'subject_group' => 'คณิตศาสตร์',          'subject_type' => 'พื้นฐาน', 'credits' => 1.5],
                    ['code' => 'ว21101', 'name_th' => 'วิทยาศาสตร์',         'subject_group' => 'วิทยาศาสตร์',         'subject_type' => 'พื้นฐาน', 'credits' => 1.5],
                    ['code' => 'ส21101', 'name_th' => 'สังคมศึกษา',          'subject_group' => 'สังคมศึกษา',          'subject_type' => 'พื้นฐาน', 'credits' => 1.5],
                    ['code' => 'อ21101', 'name_th' => 'ภาษาอังกฤษ',          'subject_group' => 'ภาษาต่างประเทศ',      'subject_type' => 'พื้นฐาน', 'credits' => 1.5],
                    ['code' => 'พ21101', 'name_th' => 'สุขศึกษาและพลศึกษา',  'subject_group' => 'สุขศึกษาและพลศึกษา',  'subject_type' => 'พื้นฐาน', 'credits' => 1.0],
                    ['code' => 'ศ21101', 'name_th' => 'ศิลปะ',               'subject_group' => 'ศิลปะ',               'subject_type' => 'พื้นฐาน', 'credits' => 1.0],
                    ['code' => 'ง21101', 'name_th' => 'การงานอาชีพ',         'subject_group' => 'การงานอาชีพ',         'subject_type' => 'พื้นฐาน', 'credits' => 1.0],
                ];
                $subjects = collect();
                foreach ($fakeSubjects as $fs) {
                    $subjects->push(Subject::create($fs + ['is_active' => true]));
                }
                $this->line('  - สร้างรายวิชาปลอม ' . $subjects->count() . ' วิชา (ไม่มีวิชาในระบบเลย)');
            }

            foreach ($subjects as $subj) {
                TeachingAssign::firstOrCreate(
                    ['section_id' => $section->section_id, 'subject_id' => $subj->subject_id, 'semester_id' => $semesterId],
                    ['personnel_id' => $teacher->personnel_id]
                );
            }
            $this->line("  - มอบหมายวิชาให้ห้องนี้ {$subjects->count()} วิชา (ครูผู้สอน: {$teacher->thai_firstname})");
        }

        // ผลการเรียนทุกวิชาที่สอนในห้องนี้ (ใช้ใน ปพ.1, ปพ.3, ปพ.5, ปพ.6)
        $assigns = TeachingAssign::where('section_id', $section->section_id)->get();

        // ตารางสอน (ใช้ใน ปพ.5 หน้าบันทึกเวลาเรียน — ถ้าวิชาไหนไม่มีตารางสอนเลย ระบบจะไม่รู้ว่าสอนวันไหน เลยไม่สร้างหน้านี้ให้)
        $slotCount = 0;
        foreach ($assigns as $assign) {
            if (TimetableSlot::where('assign_id', $assign->assign_id)->count() > 0) continue;
            TimetableSlot::create(['assign_id' => $assign->assign_id, 'day_of_week' => 'จันทร์', 'start_time' => '08:30', 'end_time' => '09:20']);
            TimetableSlot::create(['assign_id' => $assign->assign_id, 'day_of_week' => 'พุธ', 'start_time' => '08:30', 'end_time' => '09:20']);
            $slotCount++;
        }
        if ($slotCount) $this->line("  - เติมตารางสอน (ปลอม จันทร์+พุธ) ให้ {$slotCount} วิชาที่ยังไม่มีตารางสอน");

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

        // ผลประเมินคุณลักษณะ/การอ่านคิดวิเคราะห์ภาพรวมรายภาคเรียน (ใช้ใน ปพ.6)
        StudentAssessment::firstOrCreate(
            ['student_id' => $studentId, 'semester_id' => $semesterId],
            ['reading_thinking' => 'ดีเยี่ยม', 'desired_char' => 'ดีเยี่ยม', 'activity' => 'ผ่าน']
        );
        $this->line('  - สร้างผลประเมินคุณลักษณะ/การอ่านคิดวิเคราะห์ภาพรวม (ปลอม)');

        $this->newLine();
        $this->info("เสร็จแล้ว! ทดสอบพิมพ์เอกสารของนักเรียนคนนี้ได้เลย (section_id={$section->section_id}, semester_id={$semesterId})");
        $this->warn("ข้อมูลของนักเรียนที่สร้างเป็นข้อมูลปลอม ก่อนใช้งานจริงให้รัน: php artisan test:unseed-por-data {$studentId}");
        $this->comment('หมายเหตุ: ถ้าคำสั่งนี้สร้างวิชา/ครูผู้สอนปลอมให้ห้องด้วย (ตอนที่ห้องยังไม่มีวิชาเลย) ตัวเลือก unseed จะไม่ลบวิชา/ครูผู้สอนออกให้ เพราะอาจถูกห้องอื่นใช้ร่วมด้วย ต้องไปลบเองที่หน้าจัดตารางสอนถ้าไม่ต้องการ');
        $this->comment('หมายเหตุ: วันเริ่ม-สิ้นสุดภาคเรียน และตารางสอนที่เติมให้ (ถ้ายังไม่มี) เป็นข้อมูลที่ใช้ร่วมกันทั้งภาคเรียน/ห้องเรียน ตัวเลือก unseed จะไม่ลบให้เช่นกัน ต้องไปแก้ที่หน้าตั้งค่าภาคเรียน/จัดตารางสอนเอง');

        return 0;
    }
}
