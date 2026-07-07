<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('year_id')->nullable(); // ผูกกับปีการศึกษา (academic_years.year_id)
            $table->string('title');                           // ชื่อวันหยุด
            $table->date('start_date');                        // วันเริ่ม
            $table->date('end_date')->nullable();              // วันสิ้นสุด (กรณีหยุดหลายวัน)
            $table->string('note')->nullable();                // หมายเหตุ
            $table->timestamps();

            $table->index('year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
