<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ยกเลิกฟีเจอร์สัดส่วนคะแนนอ้างอิงในแผน — สุดท้ายข้อมูลนี้ควรอยู่ที่หน้าบันทึกคะแนนจริงแทน
        // (ที่ครูตั้งชื่อหมวดคะแนนเองต่อห้องอยู่แล้วผ่าน score_categories) ไม่ใช่ข้อมูลระดับแผน
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

    public function down(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->boolean('teacher_can_edit_score_ratio')->default(true)->after('is_active');
            $table->decimal('score_collect_pct', 5, 1)->nullable()->after('teacher_can_edit_score_ratio');
            $table->decimal('score_collect_after_midterm_pct', 5, 1)->nullable()->after('score_collect_pct');
            $table->decimal('midterm_pct', 5, 1)->nullable()->after('score_collect_after_midterm_pct');
            $table->decimal('final_pct', 5, 1)->nullable()->after('midterm_pct');
            $table->decimal('pass_threshold_pct', 5, 1)->nullable()->after('final_pct');
        });
    }
};
