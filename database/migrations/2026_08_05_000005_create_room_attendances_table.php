<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // เช็คชื่อ "รวมทั้งห้อง" ต่อวัน — อิสระจากเช็คชื่อรายวิชา (class_attendances) ไม่ต้องเปิดเช็คทีละวิชา
        // เช็คครั้งเดียวต่อห้องต่อวัน มีแค่ 2 สถานะ (มา/ไม่มา) ต่างจากเช็คชื่อรายวิชาที่มี 4 สถานะ
        Schema::create('room_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('student_id');
            $table->date('class_date');
            $table->string('status'); // มา / ไม่มา
            $table->timestamps();

            $table->unique(['section_id', 'student_id', 'class_date']);
            $table->index('class_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_attendances');
    }
};
