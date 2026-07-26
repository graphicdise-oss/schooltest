<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ใช้ '' แทน null สำหรับห้องที่ไม่มีแผนการเรียน เพราะ unique constraint ของ Postgres
        // มองว่า null แต่ละแถวไม่ซ้ำกันเสมอ (จะทำให้สร้างห้องซ้ำแบบไม่มีแผนการเรียนได้โดยไม่ถูกกัน)
        DB::table('class_sections')->whereNull('study_plan')->update(['study_plan' => '']);

        DB::statement('ALTER TABLE class_sections ALTER COLUMN study_plan SET DEFAULT \'\'');
        DB::statement('ALTER TABLE class_sections ALTER COLUMN study_plan SET NOT NULL');

        DB::statement('ALTER TABLE class_sections DROP CONSTRAINT uq_section');
        DB::statement('ALTER TABLE class_sections ADD CONSTRAINT uq_section UNIQUE (semester_id, level_id, section_number, study_plan)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE class_sections DROP CONSTRAINT uq_section');
        DB::statement('ALTER TABLE class_sections ADD CONSTRAINT uq_section UNIQUE (semester_id, level_id, section_number)');

        DB::statement('ALTER TABLE class_sections ALTER COLUMN study_plan DROP NOT NULL');
        DB::statement('ALTER TABLE class_sections ALTER COLUMN study_plan DROP DEFAULT');
    }
};
