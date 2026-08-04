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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportTranscriptFromExcel extends Command
{
    // ใช้เลขห้อง/แผนการเรียนที่ไม่ซ้ำกับห้องจริงแน่ๆ (คนละชุดกับ grade:seed-por1-demo กันสับสน)
    private const IMPORT_SECTION_NUMBER = 9998;
    private const IMPORT_STUDY_PLAN = 'นำเข้าเกรดจากไฟล์ (ไม่ใช่ห้องเรียนจริง)';
    private const IMPORT_TEACHER_CODE = 'GRADE-IMPORT';

    protected $signature = 'import:transcript
        {studentId : student_id ของนักเรียนที่มีอยู่แล้วในระบบ}
        {file? : พาธไฟล์ .xlsx (ไม่ต้องใส่ถ้าใช้ --undo)}
        {--dry-run : ทดสอบเฉยๆ ไม่บันทึกจริง}
        {--undo : ลบข้อมูลที่นำเข้าให้นักเรียนคนนี้ทิ้งทั้งหมด}';

    protected $description = 'นำเข้าเกรดรวม (Transcript) ของนักเรียนคนเดียวจากไฟล์ Excel '
        . 'รูปแบบ: สูงสุด 3 ปีการศึกษา/ระดับชั้น เคียงกัน แต่ละกลุ่มมีแถวปีการศึกษา+ระดับชั้น, '
        . 'แถวคั่นภาคเรียน "ภาคเรียนที่ 1"/"ภาคเรียนที่ 2", และแถววิชา "รหัสวิชา : ชื่อวิชา", หน่วยกิต, เกรด (0-4)';

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

        [$data, $warnings] = $this->parseFile($path);

        if (empty($data)) {
            $this->error('ไม่พบข้อมูลผลการเรียนในไฟล์ที่อัปโหลด ตรวจสอบว่าใช้แบบฟอร์มที่ถูกต้อง (ต้องมีแถวที่มีคำว่า "ปีการศึกษา")');
            foreach ($warnings as $w) {
                $this->line("  - {$w}");
            }
            return self::FAILURE;
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่บันทึกข้อมูลจริง ===' : '=== กำลังบันทึกข้อมูลจริง ===');

        $created = 0;
        $updated = 0;

        try {
            DB::transaction(function () use ($student, $data, &$created, &$updated) {
                foreach ($data as $yearName => $block) {
                    $year = AcademicYear::firstOrCreate(['year_name' => $yearName]);
                    $level = Level::firstOrCreate(
                        ['name' => $block['level']],
                        ['level_group' => $this->guessLevelGroup($block['level']), 'sort_order' => (Level::max('sort_order') ?? 0) + 1]
                    );

                    foreach ($block['semesters'] as $semesterName => $subjects) {
                        $semester = Semester::firstOrCreate(['year_id' => $year->year_id, 'semester_name' => $semesterName]);

                        $section = ClassSection::firstOrCreate(
                            [
                                'semester_id' => $semester->semester_id,
                                'level_id' => $level->level_id,
                                'section_number' => self::IMPORT_SECTION_NUMBER,
                                'study_plan' => self::IMPORT_STUDY_PLAN,
                            ],
                            []
                        );

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

    /**
     * อ่านไฟล์ Excel — หาแถว "หัวปีการศึกษา" ก่อน (แถวที่มีคำว่า "ปีการศึกษา" อยู่ในคอลัมน์ 1, 4 หรือ 7)
     * เพื่อให้ใช้ได้ทั้งไฟล์ดิบ (หัวอยู่แถว 1) และไฟล์แบบฟอร์มที่ระบบสร้างให้ (หัวอยู่หลังแถบชื่อโรงเรียน)
     * จากนั้นอ่านลงมาทีละแถว แยกทีละกลุ่ม (สูงสุด 3 กลุ่ม, กลุ่มละ 3 คอลัมน์) — แถวที่มีคำว่า "ภาคเรียน"
     * คือการสลับภาคเรียนของกลุ่มนั้น ส่วนแถวอื่นๆ ที่มีรูปแบบ "รหัสวิชา : ชื่อวิชา" คือแถววิชา
     */
    private function parseFile(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $get = fn (int $col, int $row) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));

        $startsWithYear = fn (string $text): bool => str_starts_with(trim($text), 'ปีการศึกษา');

        $headerRow = null;
        for ($row = 1; $row <= min(20, $highestRow); $row++) {
            if ($startsWithYear($get(1, $row)) || $startsWithYear($get(4, $row)) || $startsWithYear($get(7, $row))) {
                $headerRow = $row;
                break;
            }
        }

        $warnings = [];
        if ($headerRow === null) {
            return [[], ['ไม่พบแถวที่มีคำว่า "ปีการศึกษา" ในไฟล์นี้เลย']];
        }

        $groups = [];
        for ($g = 0; $g < 3; $g++) {
            $col = 1 + $g * 3;
            $header = $get($col, $headerRow);
            if ($header === '' || !$startsWithYear($header)) {
                continue;
            }
            [$year, $level] = $this->parseYearLevel($header);
            if (!$year || !$level) {
                $warnings[] = "อ่านปีการศึกษา/ระดับชั้นจากข้อความ \"{$header}\" ไม่ได้ — ข้ามกลุ่มนี้ทั้งกลุ่ม";
                continue;
            }
            $groups[] = ['col' => $col, 'year' => $year, 'level' => $level, 'semester' => '1'];
        }

        $data = [];

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            foreach ($groups as $gi => $grp) {
                $col = $grp['col'];
                $c1 = $get($col, $row);
                $c2 = $get($col + 1, $row);
                $c3 = $get($col + 2, $row);

                if ($c1 === '' && $c2 === '' && $c3 === '') {
                    continue;
                }

                if (str_contains($c1, 'ภาคเรียน')) {
                    if (preg_match('/(\d+)/u', $c1, $m)) {
                        $groups[$gi]['semester'] = $m[1];
                    }
                    continue;
                }

                if (!preg_match('/^(\S+)\s*:\s*(.+)$/u', $c1, $m)) {
                    $warnings[] = "แถว {$row} ({$grp['year']} {$grp['level']}): อ่านชื่อวิชาไม่ได้จากค่า \"{$c1}\" — ข้าม";
                    continue;
                }
                $code = trim($m[1]);
                $name = trim($m[2]);
                $credit = is_numeric($c2) ? (float) $c2 : 0;

                if (!is_numeric($c3)) {
                    $warnings[] = "แถว {$row} วิชา {$code} {$name}: เกรด \"{$c3}\" ไม่ใช่ตัวเลข (0-4) — ข้าม";
                    continue;
                }
                $grade = (float) $c3;
                if ($grade < 0 || $grade > 4) {
                    $warnings[] = "แถว {$row} วิชา {$code} {$name}: เกรด {$grade} ต้องอยู่ระหว่าง 0-4 — ข้าม";
                    continue;
                }

                $sem = $groups[$gi]['semester'];
                $data[$grp['year']]['level'] = $grp['level'];
                $data[$grp['year']]['semesters'][$sem][] = [$code, $name, $credit, $grade];
            }
        }

        return [$data, $warnings];
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

    /**
     * ลบข้อมูลที่คำสั่งนี้เคยนำเข้าให้นักเรียนคนนี้ทิ้ง — ไล่จาก "ครูนำเข้า" (GRADE-IMPORT) ซึ่งมีแค่คำสั่งนี้เท่านั้นที่ใช้
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
        $this->info(($dryRun ? 'จะลบผลการเรียน: ' : 'ลบผลการเรียน: ') . "{$gradeCount} รายวิชา");
        $this->info(($dryRun ? 'จะลบการอยู่ในห้องนำเข้า: ' : 'ลบการอยู่ในห้องนำเข้า: ') . "{$sectionMemberCount} รายการ");

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
