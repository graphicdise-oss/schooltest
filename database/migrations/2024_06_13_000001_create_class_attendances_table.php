<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assign_id');  // teaching_assigns.assign_id (รายวิชา+ห้อง+เทอม)
            $table->unsignedBigInteger('student_id');
            $table->date('class_date');
            $table->string('status')->default('มา'); // มา / ป่วย / ลา / ขาด
            $table->timestamps();

            $table->unique(['assign_id', 'student_id', 'class_date']);
            $table->index('class_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_attendances');
    }
};
