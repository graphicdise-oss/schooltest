<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('reading_thinking')->nullable(); // ผลการอ่าน คิดวิเคราะห์และเขียน
            $table->string('desired_char')->nullable();      // คุณลักษณะอันพึงประสงค์
            $table->string('activity')->nullable();          // กิจกรรมพัฒนาผู้เรียน (ผ่าน/ไม่ผ่าน)
            $table->timestamps();

            $table->unique(['student_id', 'semester_id']);
            $table->index('semester_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_assessments');
    }
};
