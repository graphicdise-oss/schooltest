<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReconcileMigrationHistory extends Command
{
    protected $signature = 'db:reconcile-migrations {--dry-run}';

    protected $description = 'ตรวจ migration เก่าที่ขึ้น "Pending" ทั้งที่ตาราง/คอลัมน์มีอยู่แล้วจริงในฐานข้อมูล '
        . '(เช่น ตาราง users ที่มีมาจากตอนติดตั้งโปรเจกต์ครั้งแรก แต่ไม่เคยถูกบันทึกในตาราง migrations) '
        . 'แล้วบันทึกว่า "รันแล้ว" ให้ โดยไม่ได้ไปรัน SQL สร้างตารางซ้ำ (จะได้ไม่ error relation already exists)';

    // migration => callback เช็คว่าสิ่งที่ migration นี้จะสร้าง มีอยู่แล้วในฐานข้อมูลหรือยัง
    // เฉพาะ migration ที่ "ไม่มี" การเช็ค Schema::hasTable/hasColumn ป้องกันตัวเองอยู่แล้วภายใน — พวกที่การันตีความปลอดภัยอยู่แล้ว
    // (เช่น add_registrar_to_pp2_settings, normalize_personnel_roles) ปล่อยให้ `php artisan migrate` รันตามปกติได้เลย ไม่ต้องเช็คตรงนี้
    private function checks(): array
    {
        return [
            '0001_01_01_000000_create_users_table' => fn () => Schema::hasTable('users'),
            '0001_01_01_000001_create_cache_table' => fn () => Schema::hasTable('cache'),
            '0001_01_01_000002_create_jobs_table' => fn () => Schema::hasTable('jobs'),
            '2024_01_01_000010_create_student_types_table' => fn () => Schema::hasTable('student_types'),
            '2024_06_01_000001_add_is_checkbox_to_score_categories' => fn () => Schema::hasColumn('score_categories', 'is_checkbox'),
            '2024_06_02_000001_create_student_doc_numbers_table' => fn () => Schema::hasTable('student_doc_numbers'),
            '2026_07_20_000001_add_lunch_time_to_class_sections_table' => fn () => Schema::hasColumn('class_sections', 'lunch_start'),
        ];
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $alreadyTracked = DB::table('migrations')->pluck('migration')->all();
        $nextBatch = (DB::table('migrations')->max('batch') ?? 0) + 1;

        $marked = 0;
        $left = 0;

        foreach ($this->checks() as $migrationName => $checkFn) {
            if (in_array($migrationName, $alreadyTracked, true)) {
                $this->line("[{$migrationName}] บันทึกไว้แล้วว่ารันแล้ว — ข้าม");
                continue;
            }

            if (!file_exists(database_path("migrations/{$migrationName}.php"))) {
                $this->warn("[{$migrationName}] ไม่พบไฟล์นี้ในโปรเจกต์ — ข้าม");
                continue;
            }

            if ($checkFn()) {
                $this->info("[{$migrationName}] มีอยู่แล้วในฐานข้อมูลจริง -> บันทึกว่ารันแล้ว (ไม่ได้ไปรัน SQL ซ้ำ)");
                if (!$dryRun) {
                    DB::table('migrations')->insert(['migration' => $migrationName, 'batch' => $nextBatch]);
                }
                $marked++;
            } else {
                $this->line("[{$migrationName}] ยังไม่มีในฐานข้อมูล -> ปล่อยให้ `php artisan migrate` สร้างให้ตามปกติ");
                $left++;
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->comment("โหมดทดสอบ: จะบันทึกว่ารันแล้ว {$marked} รายการ (ยังไม่ได้บันทึกจริง) เหลือให้ migrate จัดการ {$left} รายการ");
            $this->comment('รันคำสั่งเดิมโดยไม่ใส่ --dry-run เพื่อบันทึกจริง');
        } else {
            $this->info("บันทึกว่ารันแล้ว {$marked} รายการ เหลือให้ `php artisan migrate` จัดการอีก {$left} รายการ");
            $this->comment('ขั้นตอนต่อไป: รัน `php artisan migrate` อีกครั้ง ควรจะผ่านหมดโดยไม่ error แล้ว');
        }

        return self::SUCCESS;
    }
}
