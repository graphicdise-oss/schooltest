<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // เปลี่ยนคุณลักษณะอันพึงประสงค์จาก 8 ข้อคุณลักษณะแห่งชาติแบบตายตัว (char_1-8)
    // เป็นให้ครูกำหนดจำนวน "หน่วยที่" ของวิชาตัวเองได้ (ตามแบบฟอร์ม ปพ.5 จริงที่โรงเรียนใช้ ซึ่งประเมินเป็นรายหน่วยการเรียน)
    public function up(): void
    {
        Schema::table('subject_assessments', function (Blueprint $table) {
            $cols = [];
            for ($i = 1; $i <= 8; $i++) $cols[] = "char_{$i}";
            $table->dropColumn($cols);
            $table->json('char_units')->nullable()->after('competency'); // [0-3, 0-3, ...] ตามจำนวนหน่วยของวิชา
        });

        Schema::table('teaching_assigns', function (Blueprint $table) {
            $table->unsignedTinyInteger('char_unit_count')->default(8)->after('semester_id');
        });
    }

    public function down(): void
    {
        Schema::table('teaching_assigns', function (Blueprint $table) {
            $table->dropColumn('char_unit_count');
        });

        Schema::table('subject_assessments', function (Blueprint $table) {
            $table->dropColumn('char_units');
            for ($i = 1; $i <= 8; $i++) {
                $table->unsignedTinyInteger("char_{$i}")->nullable();
            }
        });
    }
};
