<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ผลการประเมินคุณภาพผู้เรียน "รายวิชา" (ใช้สำหรับ ปพ.5) — แยกจาก student_assessments
    // ซึ่งเป็นผลประเมินภาพรวมระดับภาคเรียน/ครูประจำชั้น (ใช้กับ ปพ.1)
    public function up(): void
    {
        Schema::create('subject_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assign_id'); // teaching_assigns.assign_id
            $table->unsignedBigInteger('student_id');
            $table->string('desired_char')->nullable();     // คุณลักษณะอันพึงประสงค์
            $table->string('reading_thinking')->nullable();  // การอ่านคิดวิเคราะห์และเขียน
            $table->string('competency')->nullable();        // สมรรถนะที่สำคัญของผู้เรียน
            $table->timestamps();

            $table->unique(['assign_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_assessments');
    }
};
