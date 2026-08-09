<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// ย้อนกลับ 2026_08_05_000005_create_room_attendances_table.php — เช็คชื่อรวมทั้งห้องไม่ได้ใช้งานจริง
// (ผู้ใช้ยืนยันว่าไม่ใช้ฟีเจอร์นี้) เก็บไฟล์ migration เดิมไว้เป็นประวัติ ไม่แก้ไข/ลบทิ้ง
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('room_attendances');
    }

    public function down(): void
    {
        Schema::create('room_attendances', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('student_id');
            $table->date('class_date');
            $table->string('status');
            $table->timestamps();

            $table->unique(['section_id', 'student_id', 'class_date']);
            $table->index('class_date');
        });
    }
};
