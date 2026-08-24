<?php

namespace App\Console\Commands\Concerns;

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

/**
 * ตรรกะเขียนผลการเรียน/กิจกรรมพัฒนาผู้เรียนลง DB ที่ import:transcript (ทีละคน) และ
 * import:transcript-bulk (ทีละห้อง) ใช้ร่วมกัน — เอาไว้ในที่เดียวกันตัวเดียว กัน 2 คำสั่งพากันเพี้ยน
 * ไปคนละทางถ้าต้องแก้กติกาการนำเข้าในอนาคต (เช่น เปลี่ยนวิธีคำนวณคะแนน หรือ field ที่ set ตอนสร้างวิชา)
 */
trait WritesTranscriptGrades
{
    // ใช้เลขห้อง/แผนการเรียนที่ไม่ซ้ำกับห้องจริงแน่ๆ (คนละชุดกับ grade:seed-por1-demo กันสับสน)
    private const IMPORT_SECTION_NUMBER = 9998;
    private const IMPORT_STUDY_PLAN = 'นำเข้าเกรดจากไฟล์ (ไม่ใช่ห้องเรียนจริง)';
    private const IMPORT_TEACHER_CODE = 'GRADE-IMPORT';

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
        'IS' => 'การศึกษาค้นคว้าด้วยตนเอง',
    ];

    /**
     * เขียนผลการเรียน + กิจกรรมพัฒนาผู้เรียนของนักเรียน "คนเดียว" ลง DB ตามโครงสร้าง $data/$activities
     * ที่ parseFile()-แบบต่างๆ คืนมา (รูปแบบเดียวกันทั้งไฟล์เดี่ยวและไฟล์รวมทั้งห้อง) — ไม่ครอบ DB::transaction
     * เอง (ผู้เรียกเป็นคนคุมขอบเขต transaction เอง จะได้รวมหลายคนไว้ใน transaction เดียวได้ตอนนำเข้าทั้งห้อง)
     *
     * โครงสร้าง $data: [$yearName => ['level' => string, 'semesters' => [$semName => [[code,name,credit,grade], ...]]]]
     * โครงสร้าง $activities: เหมือนกันแต่แต่ละแถวเป็น [name, hours, grade, remark]
     *
     * คืนค่า ['created'=>int,'updated'=>int,'actCreated'=>int,'actUpdated'=>int] และเติม $warnings เพิ่มถ้ามี
     * กิจกรรมของปี/เทอมที่ไม่มีตารางรายวิชาคู่กันเลย
     */
    private function writeStudentTranscript(Student $student, array $data, array $activities, array &$warnings): array
    {
        $created = 0;
        $updated = 0;
        $actCreated = 0;
        $actUpdated = 0;
        $consumedActivityKeys = [];

        foreach ($data as $yearName => $block) {
            $year = AcademicYear::firstOrCreate(['year_name' => $yearName]);
            $level = Level::firstOrCreate(
                ['name' => $block['level']],
                ['level_group' => $this->guessLevelGroup($block['level']), 'sort_order' => (Level::max('sort_order') ?? 0) + 1]
            );

            foreach ($block['semesters'] as $semesterName => $subjects) {
                $semester = Semester::firstOrCreate(['year_id' => $year->year_id, 'semester_name' => $semesterName]);

                $roomText = trim($block['rooms'][$semesterName] ?? '');

                if ($roomText !== '') {
                    // ไฟล์ระบุห้องมาตรงๆ (แถว "ห้อง") — ใช้ห้องนั้นเลย หา/สร้างห้องจริงตามเลขห้องที่ระบุ
                    // (ไม่ทับ study_plan ของห้องที่มีอยู่แล้ว ใช้ค่าจากไฟล์แค่ตอนต้องสร้างห้องใหม่เท่านั้น)
                    [$sectionNumber, $studyPlan] = $this->parseRoomText($roomText);
                    $section = ClassSection::firstOrCreate(
                        [
                            'semester_id' => $semester->semester_id,
                            'level_id' => $level->level_id,
                            'section_number' => $sectionNumber,
                        ],
                        ['study_plan' => $studyPlan]
                    );
                } else {
                    // ไฟล์ไม่ได้ระบุห้องมา — หาห้องเรียนจริงที่นักเรียนคนนี้เคยอยู่ในปี/เทอมนี้ก่อน (จาก
                    // student_sections ที่มีอยู่แล้วในระบบ) ถ้าเจอให้ใช้ห้องจริงเลย ไม่ต้องสร้างห้องปลอม —
                    // ใช้ห้องปลอม (9998) เฉพาะตอนหาห้องจริงไม่เจอจริงๆ เท่านั้น
                    $section = ClassSection::real()
                        ->where('semester_id', $semester->semester_id)
                        ->where('level_id', $level->level_id)
                        ->whereHas('studentSections', fn ($q) => $q->where('student_id', $student->student_id))
                        ->first();

                    $section ??= ClassSection::firstOrCreate(
                        [
                            'semester_id' => $semester->semester_id,
                            'level_id' => $level->level_id,
                            'section_number' => self::IMPORT_SECTION_NUMBER,
                            'study_plan' => self::IMPORT_STUDY_PLAN,
                        ],
                        []
                    );
                }

                $teacher = Personnel::firstOrCreate(
                    ['employee_code' => self::IMPORT_TEACHER_CODE],
                    ['thai_prefix' => '', 'thai_firstname' => 'นำเข้าเกรด', 'thai_lastname' => '(ระบบ)', 'personnel_type' => 'ครู', 'status' => 'ปฏิบัติงาน']
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

                // กิจกรรมพัฒนาผู้เรียนของปี/เทอมนี้ (ถ้ามีในไฟล์) — ใช้ห้อง/ครูนำเข้าตัวเดียวกับรายวิชาข้างบน
                // แต่ subject_group ตั้งใจให้เป็น "กิจกรรมพัฒนาผู้เรียน" ตรงตัว (por1_print.blade.php แยกไปอีกตาราง)
                $consumedActivityKeys["{$yearName}|{$semesterName}"] = true;
                foreach (($activities[$yearName]['semesters'][$semesterName] ?? []) as [$actName, $hours, $actGrade, $actRemark]) {
                    $actCode = 'ACT-' . strtoupper(substr(md5($yearName . '|' . $semesterName . '|' . $actName), 0, 8));

                    $actSubject = Subject::firstOrCreate(
                        ['code' => $actCode],
                        [
                            'name_th' => $actName,
                            'credits' => 0,
                            'subject_type' => 'กิจกรรมพัฒนาผู้เรียน',
                            'subject_group' => 'กิจกรรมพัฒนาผู้เรียน',
                            'hours_per_year' => $hours,
                            'is_active' => true,
                        ]
                    );

                    $actAssign = TeachingAssign::firstOrCreate([
                        'personnel_id' => $teacher->personnel_id,
                        'subject_id' => $actSubject->subject_id,
                        'section_id' => $section->section_id,
                        'semester_id' => $semester->semester_id,
                    ]);

                    $existingAct = FinalGrade::where([
                        'student_id' => $student->student_id,
                        'assign_id' => $actAssign->assign_id,
                        'semester_id' => $semester->semester_id,
                    ])->exists();

                    FinalGrade::updateOrCreate(
                        ['student_id' => $student->student_id, 'assign_id' => $actAssign->assign_id, 'semester_id' => $semester->semester_id],
                        ['total_score' => null, 'grade' => $actGrade, 'gpa_point' => null, 'remark' => $actRemark]
                    );

                    $existingAct ? $actUpdated++ : $actCreated++;
                }
            }
        }

        // กิจกรรมของปี/เทอมที่ไม่มีตารางรายวิชาคู่กันเลย (ไฟล์กรอกไม่ครบ) — ข้ามไปพร้อมแจ้งเตือน กันสร้างปี/ห้องลอยๆ
        foreach ($activities as $ayear => $ablock) {
            foreach ($ablock['semesters'] as $asem => $items) {
                if (!isset($consumedActivityKeys["{$ayear}|{$asem}"])) {
                    $studentLabel = "{$student->thai_prefix}{$student->thai_firstname} {$student->thai_lastname} ({$student->student_code})";
                    $warnings[] = "{$studentLabel}: กิจกรรมของปีการศึกษา {$ayear} ภาคเรียนที่ {$asem} ไม่มีตารางผลการเรียนรายวิชาของปี/เทอมเดียวกันในไฟล์ — ข้ามไป (ต้องกรอกวิชาอย่างน้อย 1 วิชาของปี/เทอมนั้นด้วย)";
                }
            }
        }

        return ['created' => $created, 'updated' => $updated, 'actCreated' => $actCreated, 'actUpdated' => $actUpdated];
    }

    // "ปีการศึกษา 2567 มัธยมศึกษาปีที่ 4" => ['2567', 'ม.4']
    private function parseYearLevel(string $text): array
    {
        if (!preg_match('/(\d{4})/u', $text, $ym)) {
            return [null, null];
        }
        $year = $ym[1];
        $rest = trim(str_replace(['ปีการศึกษา', $year], '', $text));
        if ($rest === '') {
            return [$year, null];
        }
        return [$year, $this->shortenLevel($rest)];
    }

    private function shortenLevel(string $text): string
    {
        if (preg_match('/มัธยมศึกษาปีที่\s*(\d+)/u', $text, $m)) {
            return 'ม.' . $m[1];
        }
        if (preg_match('/ประถมศึกษาปีที่\s*(\d+)/u', $text, $m)) {
            return 'ป.' . $m[1];
        }
        if (preg_match('/อนุบาล\s*(\d+)/u', $text, $m)) {
            return 'อ.' . $m[1];
        }
        return trim($text);
    }

    private function guessLevelGroup(string $levelName): string
    {
        if (str_starts_with($levelName, 'อ.')) {
            return 'อนุบาล';
        }
        if (str_starts_with($levelName, 'ป.')) {
            return 'ประถมศึกษา';
        }
        if (preg_match('/^ม\.(\d+)/u', $levelName, $m)) {
            return ((int) $m[1]) <= 3 ? 'มัธยมศึกษาตอนต้น' : 'มัธยมศึกษาตอนปลาย';
        }
        return '';
    }

    // แยกข้อความห้องจากไฟล์ (เช่น "2" หรือ "2 วิทย์-คณิต") ออกเป็น [เลขห้อง, แผนการเรียน]
    // เอาคำแรกเป็นเลขห้อง ที่เหลือ (ถ้ามี) เป็นแผนการเรียน — ไม่บังคับต้องมีแผนการเรียน
    private function parseRoomText(string $roomText): array
    {
        $parts = preg_split('/\s+/u', trim($roomText), 2);
        $sectionNumber = $parts[0];
        $studyPlan = $parts[1] ?? null;
        return [$sectionNumber, $studyPlan];
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
