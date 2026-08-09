<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // เปิด/ปิดใช้งานวิชานี้ "เฉพาะในแผนนี้" (ไม่กระทบวิชากลางหรือแผนอื่นที่ใช้วิชาเดียวกัน) —
        // วิชาที่ปิดใช้งานจะไม่ถูกเสนอเป็นตัวเลือกตอนจัดตารางสอน (ดู TimetableController::section())
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('personnel_id');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
