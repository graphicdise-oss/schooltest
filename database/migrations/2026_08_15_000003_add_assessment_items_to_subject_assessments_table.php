<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // เพิ่มคะแนนรายข้อ (0-3) ของคุณลักษณะอันพึงประสงค์ 8 ข้อ และการอ่านคิดวิเคราะห์และเขียน 5 ข้อ
    // ตามเกณฑ์การประเมินมาตรฐาน สพฐ. — desired_char/reading_thinking (ค่ารวม) จะคำนวณจากคะแนนรายข้อพวกนี้อัตโนมัติ
    public function up(): void
    {
        Schema::table('subject_assessments', function (Blueprint $table) {
            for ($i = 1; $i <= 8; $i++) {
                $table->unsignedTinyInteger("char_{$i}")->nullable();
            }
            for ($i = 1; $i <= 5; $i++) {
                $table->unsignedTinyInteger("read_{$i}")->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subject_assessments', function (Blueprint $table) {
            $cols = [];
            for ($i = 1; $i <= 8; $i++) $cols[] = "char_{$i}";
            for ($i = 1; $i <= 5; $i++) $cols[] = "read_{$i}";
            $table->dropColumn($cols);
        });
    }
};
