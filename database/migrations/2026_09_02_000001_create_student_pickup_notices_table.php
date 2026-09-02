<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ตารางใหม่ทั้งหมด (students เดิมมีอยู่แล้วในระบบ ไม่แตะ) เก็บการแจ้งรับ-ส่งนักเรียนที่ผู้ปกครอง
// พิมพ์แจ้งเข้ามาเองผ่านหน้าเว็บผู้ปกครอง เผื่อวันนั้นมีคนอื่นมารับแทนพ่อแม่
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_pickup_notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->date('notice_date');
            $table->time('pickup_time')->nullable();
            $table->string('pickup_person_name');
            $table->string('relationship')->nullable();
            $table->string('phone')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('notice_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_pickup_notices');
    }
};
