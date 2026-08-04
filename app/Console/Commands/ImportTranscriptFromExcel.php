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

    protected $description = 'นำเข้าเกรดรวม (Transcript) ของนักเรียนคนเดียวจากไฟล์ Excel — '
        . 'ตารางผลการเรียนรายวิชา (สูงสุด 3 ปีการศึกษา/ระดับชั้น เคียงกัน) '
        . 'และตารางกิจกรรมพัฒนาผู้เรียน (แนะแนว/ชุมนุม/ฯลฯ) แยกต่างหากด้านล่าง (ถ้ามี)';

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
        $consumedActivityKeys = [];

        try {
            DB::transaction(function () use ($student, $data, $activities, &$created, &$updated, &$actCreated, &$actUpdated, &$consumedActivityKeys, &$warnings) {
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
                            $warnings[] = "กิจกรรมของปีการศึกษา {$ayear} ภาคเรียนที่ {$asem} ไม่มีตารางผลการเรียนรายวิชาของปี/เทอมเดียวกันในไฟล์ — ข้ามไป (ต้องกรอกวิชาอย่างน้อย 1 วิชาของปี/เทอมนั้นด้วย)";
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

    /**
     * อ่านไฟล์ Excel — มี 2 ตารางที่แยกกันอิสระ:
     *  1) ตารางผลการเรียนรายวิชา — หาแถว "ปีการศึกษา..." แถวแรกในไฟล์ก่อน (ไม่สนว่าอยู่แถวไหน กันปนกับ
     *     แถบชื่อโรงเรียน/คำแนะนำที่แบบฟอร์มที่ระบบสร้างให้มีอยู่ก่อน) แล้วอ่านลงมาทีละแถว แยกทีละกลุ่ม
     *     (สูงสุด 3 กลุ่ม, กลุ่มละ 3 คอลัมน์) — แถวที่มีคำว่า "ภาคเรียน" คือการสลับภาคเรียนของกลุ่มนั้น
     *     ส่วนแถวอื่นๆ ที่มีรูปแบบ "รหัสวิชา : ชื่อวิชา" คือแถววิชา
     *  2) ตารางกิจกรรมพัฒนาผู้เรียน (ถ้ามี) — เริ่มที่แถวซึ่งคอลัมน์แรกของกลุ่มใดกลุ่มหนึ่งเป็นคำว่า "กิจกรรม"
     *     เป๊ะๆ (หัวคอลัมน์) จากนั้นมีโครงสร้างเหมือนตารางแรกทุกอย่าง ต่างกันแค่ 3 คอลัมน์คือ
     *     ชื่อกิจกรรม / เวลา (ชั่วโมง) / ผลการประเมิน (ผ = ผ่าน, มผ = ไม่ผ่าน)
     */
    private function parseFile(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $get = fn (int $col, int $row) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));
        $startsWithYear = fn (string $text): bool => str_starts_with(trim($text), 'ปีการศึกษา');

        $warnings = [];

        $subjectHeaderRow = $this->findHeaderRow($get, $highestRow, $startsWithYear, 1);
        if ($subjectHeaderRow === null) {
            return [[], [], ['ไม่พบแถวที่มีคำว่า "ปีการศึกษา" ในไฟล์นี้เลย']];
        }

        $activityLabelRow = null;
        for ($row = $subjectHeaderRow + 1; $row <= $highestRow; $row++) {
            if ($get(1, $row) === 'กิจกรรม' || $get(4, $row) === 'กิจกรรม' || $get(7, $row) === 'กิจกรรม') {
                $activityLabelRow = $row;
                break;
            }
        }
        $subjectEndRow = $activityLabelRow ? $activityLabelRow - 1 : $highestRow;

        $subjectGroups = $this->locateGroups($get, $subjectHeaderRow, $startsWithYear, $warnings);
        $data = $this->readSubjectRows($get, $subjectGroups, $subjectHeaderRow + 1, $subjectEndRow, $warnings);

        $activities = [];
        if ($activityLabelRow !== null) {
            $activityHeaderRow = $this->findHeaderRow($get, $highestRow, $startsWithYear, $activityLabelRow + 1);
            if ($activityHeaderRow !== null) {
                $activityGroups = $this->locateGroups($get, $activityHeaderRow, $startsWithYear, $warnings);
                $activities = $this->readActivityRows($get, $activityGroups, $activityHeaderRow + 1, $highestRow, $warnings);
            }
        }

        return [$data, $activities, $warnings];
    }

    // หาแถวแรก (นับจาก $fromRow) ที่คอลัมน์ 1, 4 หรือ 7 ขึ้นต้นด้วยคำว่า "ปีการศึกษา"
    private function findHeaderRow(callable $get, int $highestRow, callable $startsWithYear, int $fromRow): ?int
    {
        for ($row = $fromRow; $row <= min($fromRow + 20, $highestRow); $row++) {
            if ($startsWithYear($get(1, $row)) || $startsWithYear($get(4, $row)) || $startsWithYear($get(7, $row))) {
                return $row;
            }
        }
        return null;
    }

    // อ่านแถวหัว "ปีการศึกษา XXXX ระดับชั้น" ของทั้ง 3 กลุ่ม (คอลัมน์ 1, 4, 7) ที่แถว $headerRow
    private function locateGroups(callable $get, int $headerRow, callable $startsWithYear, array &$warnings): array
    {
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
        return $groups;
    }

    private function readSubjectRows(callable $get, array $groups, int $fromRow, int $toRow, array &$warnings): array
    {
        $data = [];

        for ($row = $fromRow; $row <= $toRow; $row++) {
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
                    // มีแค่คอลัมน์แรก ไม่มีหน่วยกิต/เกรดเลย — เป็นข้อความอื่น (เช่น แถบคำแนะนำที่ทับเข้ามาในช่วงแถว)
                    // ไม่ใช่ความตั้งใจกรอกวิชา จึงข้ามแบบเงียบๆ ไม่ต้องเตือน
                    if ($c2 !== '' || $c3 !== '') {
                        $warnings[] = "แถว {$row} ({$grp['year']} {$grp['level']}): อ่านชื่อวิชาไม่ได้จากค่า \"{$c1}\" — ข้าม";
                    }
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

        return $data;
    }

    private function readActivityRows(callable $get, array $groups, int $fromRow, int $toRow, array &$warnings): array
    {
        $activities = [];

        for ($row = $fromRow; $row <= $toRow; $row++) {
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

                $name = $c1;
                if ($name === '') {
                    $warnings[] = "แถว {$row} ({$grp['year']} {$grp['level']}): ไม่มีชื่อกิจกรรม — ข้าม";
                    continue;
                }
                $hours = is_numeric($c2) ? (float) $c2 : 0;

                $resultRaw = trim($c3);
                if ($resultRaw === '') {
                    $warnings[] = "แถว {$row} กิจกรรม {$name}: ยังไม่มีผลการประเมิน — ข้าม";
                    continue;
                }
                if ($resultRaw === 'มผ' || str_contains($resultRaw, 'ไม่ผ่าน')) {
                    $grade = 'ไม่ผ่าน';
                    $remark = 'ไม่ผ่าน';
                } elseif ($resultRaw === 'ผ' || str_contains($resultRaw, 'ผ่าน')) {
                    $grade = 'ผ่าน';
                    $remark = 'ผ่าน';
                } else {
                    $grade = $resultRaw;
                    $remark = $resultRaw;
                }

                $sem = $groups[$gi]['semester'];
                $activities[$grp['year']]['level'] = $grp['level'];
                $activities[$grp['year']]['semesters'][$sem][] = [$name, $hours, $grade, $remark];
            }
        }

        return $activities;
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
