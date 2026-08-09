<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // curriculum_subjects.hours_per_year ถูกสร้างเป็น integer แต่ subjects.hours_per_year ตัวจริง
        // ในฐานข้อมูลเป็น numeric (เช่น 20.0, 40.0) เวลาก็อปค่ามาจากวิชาแล้วบันทึกเป็น override
        // จึงชนกับชนิดข้อมูล ทำให้ insert ล้มเหลว (invalid input syntax for type integer: "20.0")
        DB::statement('ALTER TABLE curriculum_subjects ALTER COLUMN hours_per_year TYPE numeric(6,1) USING hours_per_year::numeric(6,1)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE curriculum_subjects ALTER COLUMN hours_per_year TYPE integer USING round(hours_per_year)::integer');
    }
};
