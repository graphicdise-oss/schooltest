<?php

namespace App\Console\Commands;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\FinalGrade;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Models\Academic\Subject;
use App\Models\Academic\TeachingAssign;
use App\Models\Personne\Personnel;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedPor1DemoGrades extends Command
{
    // ใช้เลขห้อง/แผนการเรียนที่ไม่ซ้ำกับห้องจริงแน่ๆ กันไปปนกับห้องเรียนจริงของโรงเรียน
    private const TEST_SECTION_NUMBER = 9999;
    private const TEST_STUDY_PLAN = 'ทดสอบเกรด (ลบได้)';
    private const TEST_TEACHER_CODE = 'GRADE-TEST';

    protected $signature = 'grade:seed-por1-demo
        {student_code : รหัสนักเรียน (student_code) ที่มีอยู่แล้วในระบบ}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}
        {--undo : ลบข้อมูลที่คำสั่งนี้เคยใส่ให้นักเรียนคนนี้ทิ้งทั้งหมด}';

    protected $description = 'ใส่ข้อมูลผลการเรียน (ตามไฟล์ตัวอย่างที่ผู้ใช้ให้มา) ให้นักเรียนคนที่ระบุ '
        . 'เพื่อทดสอบว่าใบ ปพ.1 แสดงผลถูกต้องไหม — ไม่แตะห้องเรียน/การลงทะเบียนจริงของนักเรียนคนนี้เลย '
        . 'สร้างแค่ปีการศึกษา/ภาคเรียน/ห้อง(เฉพาะที่ยังไม่มี)/รายวิชา/ครูทดสอบ (ถ้ายังไม่มี) แล้วใส่ผลการเรียน';

    // ปี => [ระดับชั้น, [ภาคเรียน => [รายวิชา]]]
    // รูปแบบรายวิชา: [รหัสวิชา, ชื่อวิชา, หน่วยกิต, เกรด (0-4)]
    private const DATA = [
        '2567' => [
            'level' => 'ม.4',
            'semesters' => [
                '1' => [
                    ['ท31101', 'ภาษาไทย 1', 1, 3.5],
                    ['ค31101', 'คณิตศาสตร์ 1', 1, 3.5],
                    ['ว31101', 'วิทยาศาสตร์ชีวภาพ 1', 1, 2.5],
                    ['ว31103', 'การออกแบบและเทคโนโลยี 1', 0.5, 4],
                    ['ส31101', 'สังคมศึกษา 1', 1, 3.5],
                    ['ส31103', 'ประวัติศาสตร์ไทย 1', 0.5, 4],
                    ['พ31101', 'สุขศึกษาและพลศึกษา 1', 0.5, 4],
                    ['ศ31101', 'ดนตรี-นาฏศิลป์ 1', 0.5, 4],
                    ['ง31101', 'งานอาชีพ 1', 0.5, 4],
                    ['อ31101', 'ภาษาอังกฤษ 1', 1, 4],
                    ['ค31201', 'คณิตศาสตร์เพิ่มเติม 1', 1.5, 2],
                    ['ว31201', 'ฟิสิกส์เพิ่มเติม 1', 1.5, 4],
                    ['ว31221', 'เคมีเพิ่มเติม 1', 1.5, 2],
                    ['ว31241', 'ชีววิทยาเพิ่มเติม 1', 1.5, 3],
                    ['ว31261', 'ดาราศาสตร์เพิ่มเติม 1', 0.5, 4],
                    ['ว31281', 'Robotics 1', 0.5, 4],
                    ['พ31201', 'พลศึกษา 1', 0.5, 4],
                    ['อ31201', 'ภาษาอังกฤษฟัง-พูด 1', 0.5, 3],
                    ['อ31203', 'ภาษาอังกฤษอ่าน-เขียน 1', 0.5, 3.5],
                ],
                '2' => [
                    ['ท31102', 'ภาษาไทย 2', 1, 4],
                    ['ค31102', 'คณิตศาสตร์ 2', 1, 2.5],
                    ['ว31102', 'วิทยาศาสตร์ชีวภาพ 2', 1, 3.5],
                    ['ว31104', 'วิทยาการคำนวณ 1', 0.5, 4],
                    ['ส31102', 'สังคมศึกษา 2', 1, 4],
                    ['ส31104', 'ประวัติศาสตร์ไทย 2', 0.5, 3],
                    ['พ31102', 'สุขศึกษาและพลศึกษา 2', 0.5, 4],
                    ['ศ31102', 'ทัศนศิลป์1', 0.5, 4],
                    ['ง31102', 'งานอาชีพ 2', 0.5, 4],
                    ['อ31102', 'ภาษาอังกฤษ 2', 1, 4],
                    ['ค31202', 'คณิตศาสตร์เพิ่มเติม 2', 1.5, 2.5],
                    ['ว31202', 'ฟิสิกส์เพิ่มเติม 2', 1.5, 3.5],
                    ['ว31222', 'เคมีเพิ่มเติม 2', 1.5, 3.5],
                    ['ว31242', 'ชีววิทยาเพิ่มเติม 2', 1.5, 2],
                    ['ว31262', 'ดาราศาสตร์เพิ่มเติม 2', 0.5, 4],
                    ['ว31282', 'Robotics 2', 0.5, 4],
                    ['พ31202', 'พลศึกษา 2', 0.5, 4],
                    ['อ31202', 'ภาษาอังกฤษฟัง-พูด 2', 0.5, 4],
                    ['อ31204', 'ภาษาอังกฤษอ่าน-เขียน 2', 0.5, 3],
                ],
            ],
        ],
        '2568' => [
            'level' => 'ม.5',
            'semesters' => [
                '1' => [
                    ['ท32101', 'ภาษาไทย 3', 1, 4],
                    ['ค32101', 'คณิตศาสตร์ 3', 1, 1.5],
                    ['ว32101', 'วิทยาศาสตร์กายภาพ 1', 1, 3],
                    ['ว32103', 'การออกแบบและเทคโนโลยี 2', 0.5, 4],
                    ['ส32101', 'สังคมศึกษา 3', 1, 3],
                    ['ส32103', 'ประวัติศาสตร์สากล 1', 0.5, 4],
                    ['พ32101', 'สุขศึกษาและพลศึกษา 3', 0.5, 4],
                    ['ศ32101', 'ทัศนศิลป์ 2', 0.5, 4],
                    ['อ32101', 'ภาษาอังกฤษ 3', 1, 3.5],
                    ['ค32201', 'คณิตศาสตร์เพิ่มเติม 3', 1.5, 2.5],
                    ['ว32201', 'ฟิสิกส์เพิ่มเติม 3', 1.5, 3],
                    ['ว32221', 'เคมีเพิ่มเติม 3', 1.5, 2],
                    ['ว32241', 'ชีววิทยาเพิ่มเติม 3', 1.5, 3],
                    ['ว32281', 'Robotics 3', 0.5, 4],
                    ['พ32201', 'พลศึกษา 3', 0.5, 4],
                    ['อ32201', 'ภาษาอังกฤษฟัง-พูด 3', 0.5, 4],
                    ['อ32203', 'ภาษาอังกฤษอ่าน-เขียน 3', 0.5, 2],
                    ['IS32201', 'การศึกษาค้นคว้าเพื่อสร้างองค์ความรู้', 1, 4],
                ],
                '2' => [
                    ['ท32102', 'ภาษาไทย 4', 1, 3.5],
                    ['ค32102', 'คณิตศาสตร์ 4', 1, 3],
                    ['ว32102', 'วิทยาศาสตร์กายภาพ 2', 1, 2.5],
                    ['ว32104', 'วิทยาการคำนวณ 4', 0.5, 4],
                    ['ส32102', 'สังคมศึกษา 4', 1, 4],
                    ['ส32104', 'ประวัติศาสตร์สากล 2', 0.5, 4],
                    ['พ32102', 'สุขศึกษาและพลศึกษา 4', 0.5, 4],
                    ['ศ32102', 'ดนตรี-นาฏศิลป์ 2', 0.5, 4],
                    ['อ32102', 'ภาษาอังกฤษ 4', 1, 4],
                    ['ค32202', 'คณิตศาสตร์เพิ่มเติม 4', 1.5, 2.5],
                    ['ว32202', 'ฟิสิกส์เพิ่มเติม 4', 1.5, 4],
                    ['ว32222', 'เคมีเพิ่มเติม 4', 1.5, 3],
                    ['ว32242', 'ชีววิทยาเพิ่มเติม 4', 1.5, 3],
                    ['ว32282', 'Robotics 4', 0.5, 4],
                    ['พ32202', 'พลศึกษา 4', 0.5, 4],
                    ['อ32202', 'ภาษาอังกฤษฟัง-พูด 4', 0.5, 4],
                    ['อ32204', 'ภาษาอังกฤษอ่าน-เขียน 4', 0.5, 4],
                    ['IS32202', 'การสื่อสารและการนำเสนอ', 1, 4],
                ],
            ],
        ],
    ];

    // กลุ่มสาระ ตามอักษรนำหน้ารหัสวิชา (มาตรฐานกระทรวงศึกษาธิการ)
    private const SUBJECT_GROUPS = [
        'ท' => 'ภาษาไทย',
        'ค' => 'คณิตศาสตร์',
        'ว' => 'วิทยาศาสตร์และเทคโนโลยี',
        'ส' => 'สังคมศึกษา ศาสนา และวัฒนธรรม',
        'พ' => 'สุขศึกษาและพลศึกษา',
        'ศ' => 'ศิลปะ',
        'ง' => 'การงานอาชีพ',
        'อ' => 'ภาษาต่างประเทศ',
        // ห้ามเป็น "กิจกรรมพัฒนาผู้เรียน" เด็ดขาด — por1_print.blade.php กรองรายวิชากลุ่มนั้นออกจากตารางเกรดหลัก
        // โดยตั้งใจ (ไปโผล่ที่ตารางกิจกรรมแยกต่างหากแทน) วิชา IS (การศึกษาค้นคว้าด้วยตนเอง) มีเกรด 0-4 จริง
        // ต้องอยู่ในตารางหลัก จึงต้องใช้ชื่อกลุ่มอื่นที่ไม่ตรงกับคำนี้เป๊ะๆ
        'IS' => 'การศึกษาค้นคว้าด้วยตนเอง',
    ];

    public function handle(): int
    {
        $studentCode = $this->argument('student_code');
        $dryRun = (bool) $this->option('dry-run');

        $student = Student::where('student_code', $studentCode)->first();
        if (!$student) {
            $this->error("ไม่พบนักเรียนรหัส {$studentCode} ในระบบ (ต้องมีอยู่แล้วเท่านั้น คำสั่งนี้ไม่สร้างนักเรียนใหม่)");
            return self::FAILURE;
        }

        $this->info("พบนักเรียน: {$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} (student_id={$student->student_id})");

        if ($this->option('undo')) {
            return $this->undo($student, $dryRun);
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');

        $created = 0;
        $updated = 0;

        try {
            DB::transaction(function () use ($student, &$created, &$updated) {
                foreach (self::DATA as $yearName => $block) {
                    $year = AcademicYear::firstOrCreate(['year_name' => $yearName]);
                    $level = Level::firstOrCreate(
                        ['name' => $block['level']],
                        ['level_group' => 'มัธยมศึกษาตอนปลาย', 'sort_order' => (Level::max('sort_order') ?? 0) + 1]
                    );

                    foreach ($block['semesters'] as $semesterName => $subjects) {
                        $semester = Semester::firstOrCreate(['year_id' => $year->year_id, 'semester_name' => $semesterName]);

                        // ใช้เลขห้อง/แผนการเรียนที่ตั้งใจให้ไม่ซ้ำกับห้องจริงของโรงเรียนเด็ดขาด
                        // (กันไปปนกับรายชื่อห้องจริงในหน้าอื่นๆ เช่น class-roster)
                        $section = ClassSection::firstOrCreate(
                            [
                                'semester_id' => $semester->semester_id,
                                'level_id' => $level->level_id,
                                'section_number' => self::TEST_SECTION_NUMBER,
                                'study_plan' => self::TEST_STUDY_PLAN,
                            ],
                            []
                        );

                        $teacher = Personnel::firstOrCreate(
                            ['employee_code' => self::TEST_TEACHER_CODE],
                            ['thai_prefix' => 'นางสาว', 'thai_firstname' => 'ทดสอบ', 'thai_lastname' => 'ระบบเกรด (ปพ.1)', 'personnel_type' => 'ครู', 'status' => 'ปฏิบัติงาน']
                        );

                        // ต้องมี student_sections ด้วย ไม่งั้นหน้า "ตั้งค่าการพิมพ์ ปพ.1" จะไม่รู้ว่านักเรียน
                        // มีเกรดเทอมนี้อยู่ (ช่องติ๊กเลือกเทอมที่จะพิมพ์ อ่านจาก student_sections ไม่ใช่ final_grades)
                        StudentSection::firstOrCreate(
                            ['student_id' => $student->student_id, 'section_id' => $section->section_id],
                            ['student_number' => 0, 'status' => 'กำลังศึกษา']
                        );

                        foreach ($subjects as [$code, $name, $credit, $grade]) {
                            $subject = Subject::firstOrCreate(
                                ['code' => $code],
                                [
                                    'name_th' => $name,
                                    'credits' => $credit,
                                    'subject_type' => $this->guessSubjectType($code),
                                    'subject_group' => $this->guessSubjectGroup($code),
                                    'is_active' => true,
                                ]
                            );

                            // แก้ของเดิมที่เคยตั้ง subject_group ผิดไว้ (รันคำสั่งเวอร์ชันก่อนหน้านี้ตั้ง IS เป็น
                            // "กิจกรรมพัฒนาผู้เรียน" ซึ่งถูกกรองออกจากตารางเกรดหลักไปโดยไม่ได้ตั้งใจ)
                            if ($subject->subject_group === 'กิจกรรมพัฒนาผู้เรียน' && str_starts_with($code, 'IS')) {
                                $subject->update(['subject_group' => $this->guessSubjectGroup($code)]);
                            }

                            $assign = TeachingAssign::firstOrCreate([
                                'personnel_id' => $teacher->personnel_id,
                                'subject_id' => $subject->subject_id,
                                'section_id' => $section->section_id,
                                'semester_id' => $semester->semester_id,
                            ]);

                            $existing = FinalGrade::where([
                                'student_id' => $student->student_id,
                                'assign_id' => $assign->assign_id,
                                'semester_id' => $semester->semester_id,
                            ])->exists();

                            FinalGrade::updateOrCreate(
                                ['student_id' => $student->student_id, 'assign_id' => $assign->assign_id, 'semester_id' => $semester->semester_id],
                                [
                                    'total_score' => $this->gradeToScore($grade),
                                    'grade' => rtrim(rtrim(number_format($grade, 1), '0'), '.'),
                                    'gpa_point' => $grade,
                                    'remark' => $grade > 0 ? 'ผ่าน' : 'ไม่ผ่าน',
                                ]
                            );

                            $existing ? $updated++ : $created++;
                        }
                    }
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
        $this->info(($dryRun ? 'จะสร้างผลการเรียนใหม่: ' : 'สร้างผลการเรียนใหม่สำเร็จ: ') . "{$created} รายวิชา");
        if ($updated > 0) {
            $this->info(($dryRun ? 'จะอัปเดตของเดิม: ' : 'อัปเดตของเดิมสำเร็จ: ') . "{$updated} รายวิชา");
        }

        if ($dryRun) {
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกบันทึกจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        } else {
            $this->comment("เข้าไปดูใบ ปพ.1 ได้ที่: /por1/print/{$student->student_id}");
        }

        return self::SUCCESS;
    }

    /**
     * ลบข้อมูลที่คำสั่งนี้เคยใส่ให้นักเรียนคนนี้ทิ้ง — ไล่จาก "ครูทดสอบ" (GRADE-TEST) ซึ่งมีแค่คำสั่งนี้เท่านั้นที่ใช้
     * จึงครอบคลุมของเก่าที่เคยรันไปแล้วด้วย ไม่ว่าจะรันเวอร์ชันไหนของคำสั่งนี้มาก่อน
     * ไม่ลบ subjects/teaching_assigns/class_sections ทิ้ง (อาจมีคนอื่นใช้ร่วม ปล่อยว่างไว้ไม่มีผลกระทบอะไร)
     */
    private function undo(Student $student, bool $dryRun): int
    {
        $teacher = Personnel::where('employee_code', self::TEST_TEACHER_CODE)->first();
        if (!$teacher) {
            $this->info('ไม่พบข้อมูลทดสอบของคำสั่งนี้ในระบบ (ไม่มีอะไรให้ลบ)');
            return self::SUCCESS;
        }

        $assignIds = TeachingAssign::where('personnel_id', $teacher->personnel_id)->pluck('assign_id');
        $sectionIds = TeachingAssign::whereIn('assign_id', $assignIds)->pluck('section_id')->unique();

        $gradeCount = FinalGrade::where('student_id', $student->student_id)->whereIn('assign_id', $assignIds)->count();
        $sectionMemberCount = StudentSection::where('student_id', $student->student_id)->whereIn('section_id', $sectionIds)->count();

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่ลบจริง ===' : '=== กำลังลบข้อมูลจริง ===');
        $this->info(($dryRun ? 'จะลบผลการเรียน: ' : 'ลบผลการเรียน: ') . "{$gradeCount} รายวิชา");
        $this->info(($dryRun ? 'จะลบการอยู่ในห้องทดสอบ: ' : 'ลบการอยู่ในห้องทดสอบ: ') . "{$sectionMemberCount} รายการ");

        if (!$dryRun) {
            FinalGrade::where('student_id', $student->student_id)->whereIn('assign_id', $assignIds)->delete();
            StudentSection::where('student_id', $student->student_id)->whereIn('section_id', $sectionIds)->delete();
            $this->comment('ลบเรียบร้อยแล้ว');
        }

        return self::SUCCESS;
    }

    // เลข 3 ตัวท้ายของรหัสวิชา ตามมาตรฐาน: ขึ้นต้นด้วย 1 = พื้นฐาน, ขึ้นต้นด้วย 2 = เพิ่มเติม
    private function guessSubjectType(string $code): string
    {
        preg_match('/(\d{3})$/', $code, $m);
        return ($m[1][0] ?? '1') === '2' ? 'เพิ่มเติม' : 'พื้นฐาน';
    }

    private function guessSubjectGroup(string $code): string
    {
        foreach (self::SUBJECT_GROUPS as $prefix => $group) {
            if (str_starts_with($code, $prefix)) {
                return $group;
            }
        }
        return 'อื่นๆ';
    }

    // แปลงเกรด (0-4) กลับเป็นคะแนนดิบโดยประมาณ (ใช้ค่ากลางของช่วงตามเกณฑ์ใน FinalGrade::calculateGrade)
    private function gradeToScore(float $grade): float
    {
        return match (true) {
            $grade >= 4 => 85,
            $grade >= 3.5 => 77,
            $grade >= 3 => 72,
            $grade >= 2.5 => 67,
            $grade >= 2 => 62,
            $grade >= 1.5 => 57,
            $grade >= 1 => 52,
            default => 25,
        };
    }
}
