<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('year_id')->nullable();
            $table->unsignedBigInteger('level_id')->nullable();   // ระดับชั้นที่สมัคร
            $table->string('applicant_phone')->nullable();
            $table->string('status')->default('รอการตรวจสอบ');    // รอการตรวจสอบ / ผ่าน / ไม่ผ่าน
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
