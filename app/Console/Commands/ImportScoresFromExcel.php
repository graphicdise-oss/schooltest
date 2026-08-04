<?php

namespace App\Console\Commands;

use App\Models\Academic\ScoreCategory;
use App\Models\Academic\StudentScore;
use App\Models\Academic\StudentSection;
use App\Models\Academic\TeachingAssign;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportScoresFromExcel extends Command
{
    protected $signature = 'import:scores
        {assignId : รหัสการมอบหมายสอน (assign_id)}
        {file : พาธไฟล์ .xlsx}
        {--dry-run : ตรวจสอบข้อมูลเฉยๆ ไม่บันทึกลงฐานข้อมูลจริง}';

    protected $description = 'นำเข้าคะแนนเก็บของนักเรียนในรายวิชา/ห้องเดียว จากไฟล์ Excel (แถวหัวตารางที่ 6, ข้อมูลเริ่มแถวที่ 7)';

    // คอลัมน์คงที่ (1-based) — ตรงกับที่ ScoreController::importTemplate() สร้างให้
    private const COL_ORDER = 2;
    private const COL_STUDENT_CODE = 3;
    private const COL_NAME = 4;
    private const FIRST_CATEGORY_COL = 5;

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        $assign = TeachingAssign::find($this->argument('assignId'));
        if (!$assign) {
            $this->error('ไม่พบรายวิชา/ห้องเรียนที่ระบุ (assign_id ไม่ถูกต้อง)');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '=== โหมดทดสอบ (dry-run) — จะไม่มีการบันทึกข้อมูลจริง ===' : '=== โหมดนำเข้าจริง ===');
        $this->newLine();

        // หมวดคะแนน เรียงลำดับเดียวกับตอนสร้างแบบฟอร์ม เพื่อจับคู่คอลัมน์ให้ตรง
        $categories = $assign->scoreCategories()->orderBy('sort_order')->get();
        if ($categories->isEmpty()) {
            $this->error('รายวิชานี้ยังไม่มีหมวดคะแนน กรุณาตั้งค่าสัดส่วนคะแนนก่อนนำเข้า');
            return self::FAILURE;
        }

        // เฉพาะนักเรียนที่กำลังศึกษาอยู่ในห้องนี้เท่านั้น ป้องกันการันำคะแนนของนักเรียนคนละห้อง/คนละวิชาเข้าผิดที่
        $enrolledStudentIds = StudentSection::where('section_id', $assign->section_id)
            ->where('status', 'กำลังศึกษา')
            ->pluck('student_id')
            ->flip();

        $sheet = IOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $updated = 0;
        $skippedBlank = 0;
        $skippedInvalid = 0;
        $errors = [];

        for ($row = 7; $row <= $highestRow; $row++) {
            $get = fn (int $col) => trim((string) ($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue() ?? ''));

            $studentCode = $get(self::COL_STUDENT_CODE);
            $name = $get(self::COL_NAME);

            if ($studentCode === '' && $name === '') {
                continue; // แถวว่าง
            }

            $label = "แถว {$row} ({$studentCode} {$name})";

            if ($studentCode === '') {
                $skippedInvalid++;
                $errors[] = "{$label}: ไม่มีรหัสนักเรียน ระบุตัวคนไม่ได้ — ข้าม";
                continue;
            }

            $student = Student::where('student_code', $studentCode)->first();
            if (!$student) {
                $skippedInvalid++;
                $errors[] = "{$label}: ไม่พบรหัสนักเรียนนี้ในระบบ — ข้าม";
                continue;
            }

            if (!$enrolledStudentIds->has($student->student_id)) {
                $skippedInvalid++;
                $errors[] = "{$label}: นักเรียนคนนี้ไม่ได้อยู่ในห้องเรียนของวิชานี้ — ข้าม";
                continue;
            }

            $rowHadScore = false;
            $rowErrors = [];
            $scoresToSave = [];

            foreach ($categories as $i => $cat) {
                $raw = $get(self::FIRST_CATEGORY_COL + $i);
                if ($raw === '') {
                    continue; // ช่องว่าง = ไม่แก้ไขคะแนนเดิมของหมวดนี้
                }
                if (!is_numeric($raw)) {
                    $rowErrors[] = "หมวด «{$cat->name}»: ค่า \"{$raw}\" ไม่ใช่ตัวเลข";
                    continue;
                }
                $value = (float) $raw;
                if ($value < 0 || $value > $cat->max_score) {
                    $rowErrors[] = "หมวด «{$cat->name}»: {$value} เกินช่วงที่กำหนด (0-{$cat->max_score})";
                    continue;
                }
                $scoresToSave[$cat->category_id] = $value;
                $rowHadScore = true;
            }

            if (!empty($rowErrors)) {
                $skippedInvalid++;
                $errors[] = "{$label}: " . implode('; ', $rowErrors);
                continue;
            }

            if (!$rowHadScore) {
                $skippedBlank++;
                continue; // ทั้งแถวไม่มีคะแนนเลย ไม่ต้องทำอะไร
            }

            try {
                DB::transaction(function () use ($student, $scoresToSave, $dryRun) {
                    foreach ($scoresToSave as $categoryId => $value) {
                        StudentScore::updateOrCreate(
                            ['category_id' => $categoryId, 'student_id' => $student->student_id],
                            ['score' => $value]
                        );
                    }
                    if ($dryRun) {
                        throw new \RuntimeException('__DRY_RUN_ROLLBACK__');
                    }
                });
                $updated++;
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === '__DRY_RUN_ROLLBACK__') {
                    $updated++;
                } else {
                    $skippedInvalid++;
                    $errors[] = "{$label}: " . $e->getMessage();
                }
            } catch (\Throwable $e) {
                $skippedInvalid++;
                $errors[] = "{$label}: " . $e->getMessage();
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'จะบันทึกคะแนนให้: ' : 'บันทึกคะแนนสำเร็จ: ') . "{$updated} คน");
        $this->info("ข้าม (ไม่มีคะแนนกรอกเลย): {$skippedBlank} คน");
        $this->info("ข้าม (ข้อมูลไม่ถูกต้อง): {$skippedInvalid} คน");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('รายละเอียดแถวที่ข้าม/ผิดพลาด:');
            foreach ($errors as $err) {
                $this->line("  - {$err}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('นี่คือผลทดสอบ ยังไม่มีข้อมูลถูกบันทึกจริง หากตรวจสอบแล้วถูกต้อง ให้รันคำสั่งเดิมโดยไม่ใส่ --dry-run');
        } else {
            $this->newLine();
            $this->comment('อย่าลืมกด "คำนวณเกรด" ในหน้าบันทึกคะแนน เพื่อคำนวณเกรดใหม่จากคะแนนที่เพิ่งนำเข้า');
        }

        return self::SUCCESS;
    }
}
