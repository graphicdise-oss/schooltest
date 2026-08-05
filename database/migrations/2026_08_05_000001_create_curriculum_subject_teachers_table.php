<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 วิชาในแผนการเรียนหนึ่ง มีครูผู้สอนได้หลายคน (เดิม curriculum_subjects.personnel_id เก็บได้แค่คนเดียว
        // ยังคงไว้เป็นครูคนหลักเพื่อความเข้ากันได้กับของเดิม ส่วนตารางนี้เก็บครูผู้สอนทุกคนของวิชานั้น)
        Schema::create('curriculum_subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculum_subject_id');
            $table->unsignedBigInteger('personnel_id');
            $table->timestamps();

            $table->unique(['curriculum_subject_id', 'personnel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_subject_teachers');
    }
};
