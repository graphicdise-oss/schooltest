<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // สัดส่วนคะแนน "อ้างอิง" ของวิชานี้ในแผนนี้ (คะแนนเก็บ/กลางภาค/ปลายภาค/% ตัดผ่าน) — เป็นข้อมูล
        // อ้างอิงสำหรับวางแผนเท่านั้น ไม่ได้ผูกกับหน้ากรอกคะแนนจริง (ที่ตั้งสัดส่วนเองอิสระต่อ TeachingAssign
        // ผ่าน score_categories อยู่แล้ว) teacher_can_edit_score_ratio ควบคุมว่าครูประจำวิชานั้นแก้ตัวเลข
        // เหล่านี้เองได้ไหม (โดยไม่ต้องพึ่งแอดมิน) — ค่านี้แก้ได้เฉพาะแอดมิน/ซูเปอร์แอดมินเท่านั้น
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->boolean('teacher_can_edit_score_ratio')->default(true)->after('is_active');
            $table->decimal('score_collect_pct', 5, 1)->nullable()->after('teacher_can_edit_score_ratio');
            $table->decimal('score_collect_after_midterm_pct', 5, 1)->nullable()->after('score_collect_pct');
            $table->decimal('midterm_pct', 5, 1)->nullable()->after('score_collect_after_midterm_pct');
            $table->decimal('final_pct', 5, 1)->nullable()->after('midterm_pct');
            $table->decimal('pass_threshold_pct', 5, 1)->nullable()->after('final_pct');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_can_edit_score_ratio',
                'score_collect_pct',
                'score_collect_after_midterm_pct',
                'midterm_pct',
                'final_pct',
                'pass_threshold_pct',
            ]);
        });
    }
};
