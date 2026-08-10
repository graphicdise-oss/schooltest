<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('personnel_id')->nullable(); // ผู้ไปเยี่ยม (ครูที่ปรึกษา)
            $table->date('visit_date');
            $table->unsignedInteger('visit_no')->default(1); // ครั้งที่ (เยี่ยมได้หลายครั้งต่อปี)
            $table->string('family_status')->nullable();     // อยู่กับใคร เช่น บิดามารดา/ญาติ/ผู้ปกครองอื่น
            $table->string('economic_status')->nullable();   // ดี/ปานกลาง/ยากจน
            $table->text('house_condition')->nullable();      // สภาพที่อยู่อาศัย
            $table->text('problems_found')->nullable();       // ปัญหาที่พบ
            $table->text('suggestion')->nullable();            // ข้อเสนอแนะ/การช่วยเหลือ
            $table->boolean('need_followup')->default(false);
            $table->string('photo')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('personnel_id');
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_visits');
    }
};
