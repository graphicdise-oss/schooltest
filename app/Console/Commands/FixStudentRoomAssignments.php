<?php

namespace App\Console\Commands;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Level;
use App\Models\Academic\Semester;
use App\Models\Academic\StudentSection;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FixStudentRoomAssignments extends Command
{
    protected $signature = 'fix:student-rooms
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ตรวจสอบเฉยๆ ไม่ย้ายจริง}
        {--semester-id= : ภาคเรียนที่จะแก้ (ค่าเริ่มต้น = ภาคเรียนปัจจุบัน)}';

    protected $description = 'ย้ายนักเรียนที่ import ไปแล้วแต่ตกอยู่ในห้องผิด (ห้องที่ไม่มีชื่อแผนการเรียน) ไปห้องที่ถูกต้องตามไฟล์ Excel — ไม่แตะข้อมูลส่วนตัว/ที่อยู่/ผู้ปกครองเลย แก้แค่การจัดห้อง';

    private const COL_ID_CARD = 5;
    private const COL_CLASS_ROOM = 2;

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $semesterId = $this->option('semester-id')
            ? (int) $this->option('semester-id')
            : Semester::where('is_current', true)->value('semester_id');

        if (!$semesterId) {
            $this->error('ไม่พบภาคเรียนปัจจุบัน กรุณาระบุ --semester-id=<รหัส> เอง');
            return self::FAILURE;
        }

        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) ===' : '=== โหมดแก้ไขจริง ===');
        $this->info("ใช้ภาคเรียน semester_id={$semesterId}");
        $this->newLine();

        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $moved = 0;
        $alreadyCorrect = 0;
        $notFound = 0;
        $unparsed = 0;
        $emptiedSections = [];
        $newLevels = [];
        $newSections = [];

        for ($row = 7; $row <= $highestRow; $row++) {
            $get = fn (int $col) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));

            $idCard = $get(self::COL_ID_CARD);
            if (strlen($idCard) !== 13) {
                continue;
            }

            $student = Student::where('id_card_number', $idCard)->first();
            if (!$student) {
                $notFound++;
                continue;
            }

            $parsed = $this->parseClassRoom($get(self::COL_CLASS_ROOM));
            if (!$parsed) {
                $unparsed++;
                continue;
            }

            $level = Level::firstOrCreate(
                ['name' => $parsed['level_name']],
                ['level_group' => null, 'sort_order' => (Level::max('sort_order') ?? 0) + 1]
            );
            if ($level->wasRecentlyCreated) {
                $newLevels[$parsed['level_name']] = true;
            }

            $correctSection = ClassSection::firstOrCreate(
                [
                    'level_id' => $level->level_id,
                    'section_number' => $parsed['section_number'],
                    'study_plan' => $parsed['study_plan'],
                    'semester_id' => $semesterId,
                ],
                ['homeroom_teacher_id' => null, 'max_students' => null, 'curriculum_id' => null]
            );
            if ($correctSection->wasRecentlyCreated) {
                $label = "{$parsed['level_name']}/{$parsed['section_number']}" . ($parsed['study_plan'] ? " {$parsed['study_plan']}" : '');
                $newSections[$label] = true;
            }

            $current = StudentSection::where('student_id', $student->student_id)
                ->whereHas('classSection', fn ($q) => $q->where('semester_id', $semesterId))
                ->first();

            if (!$current) {
                if (!$dryRun) {
                    $lastNo = StudentSection::where('section_id', $correctSection->section_id)->max('student_number') ?? 0;
                    StudentSection::create([
                        'student_id' => $student->student_id,
                        'section_id' => $correctSection->section_id,
                        'student_number' => $lastNo + 1,
                        'status' => 'กำลังศึกษา',
                    ]);
                }
                $moved++;
                continue;
            }

            if ($current->section_id === $correctSection->section_id) {
                $alreadyCorrect++;
                continue;
            }

            $oldSectionId = $current->section_id;
            if (!$dryRun) {
                $current->update(['section_id' => $correctSection->section_id]);
            }
            $moved++;

            if (!$dryRun && StudentSection::where('section_id', $oldSectionId)->count() === 0) {
                $emptiedSections[$oldSectionId] = true;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะย้าย: ' : 'ย้ายสำเร็จ: ') . "{$moved} คน");
        $this->info("อยู่ห้องถูกต้องอยู่แล้ว: {$alreadyCorrect} คน");
        $this->info("ไม่พบนักเรียนในระบบ (ยังไม่เคย import): {$notFound} คน");
        $this->info("อ่านชั้น/ห้องจากไฟล์ไม่ได้: {$unparsed} คน");

        if (!empty($newLevels)) {
            $this->info('ชั้นใหม่ที่สร้าง: ' . implode(', ', array_keys($newLevels)));
        }
        if (!empty($newSections)) {
            $this->info('ห้องใหม่ที่สร้าง: ' . implode(', ', array_keys($newSections)));
        }

        if (!empty($emptiedSections)) {
            $ids = array_keys($emptiedSections);
            $emptyRooms = ClassSection::with('level')->whereIn('section_id', $ids)->get()
                ->map(fn ($s) => "{$s->level->name}/{$s->section_number}" . ($s->study_plan ? " {$s->study_plan}" : '') . " (section_id={$s->section_id})");
            $this->newLine();
            $this->warn('ห้องต่อไปนี้ไม่มีนักเรียนเหลืออยู่แล้วหลังย้าย (ลบทิ้งเองผ่านหน้า "จัดการห้องเรียน" ได้ถ้าต้องการ ผมไม่ลบให้อัตโนมัติ):');
            foreach ($emptyRooms as $label) {
                $this->line("  - {$label}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('นี่คือผลทดสอบ ยังไม่มีการย้ายจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        }

        return self::SUCCESS;
    }

    private function parseClassRoom(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '' || !str_contains($raw, '/')) {
            return null;
        }

        [$levelName, $rest] = explode('/', $raw, 2);
        $levelName = trim($levelName);
        $rest = trim($rest);

        if ($levelName === '' || $rest === '' || !preg_match('/^(\d+)\s*(.*)$/', $rest, $m)) {
            return null;
        }

        return ['level_name' => $levelName, 'section_number' => (int) $m[1], 'study_plan' => trim($m[2])];
    }
}
