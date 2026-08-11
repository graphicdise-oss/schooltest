<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_records', function (Blueprint $table) {
            $table->id('behavior_record_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('behavior_item_id')->nullable();

            // สำเนาข้อมูลรายการพฤติกรรม ณ เวลาที่บันทึก กันประวัติเพี้ยนถ้ารายการต้นทางถูกแก้ไข/ลบภายหลัง
            $table->string('type'); // 'add' หรือ 'deduct'
            $table->string('item_name_th');
            $table->decimal('points', 5, 2); // แต้มของรายการ (ค่าบวกเสมอ)

            $table->decimal('signed_points', 6, 2); // แต้มที่มีผลจริงต่อคะแนน (+ หรือ - ตาม type)
            $table->decimal('balance_after', 6, 2); // คะแนนคงเหลือหลังรายการนี้ (ต่อปีการศึกษา/เทอมนั้น)

            $table->unsignedBigInteger('year_id')->nullable();
            $table->unsignedBigInteger('semester_id')->nullable();

            $table->text('note')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable(); // personnels.personnel_id ของผู้บันทึก

            $table->timestamps();

            $table->index('student_id');
            $table->index('year_id');
            $table->index('semester_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_records');
    }
};
