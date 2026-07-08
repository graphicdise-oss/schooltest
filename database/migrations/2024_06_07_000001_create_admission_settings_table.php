<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_open')->default(false);        // เปิดรับสมัครหรือไม่
            $table->unsignedBigInteger('year_id')->nullable(); // ปีการศึกษาที่รับสมัคร
            $table->date('open_date')->nullable();             // วันเริ่มรับสมัคร
            $table->date('close_date')->nullable();            // วันปิดรับสมัคร
            $table->text('instructions')->nullable();          // คำชี้แจง/ระเบียบการ
            $table->string('levels_note')->nullable();         // ระดับชั้นที่เปิดรับ (ข้อความ)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_settings');
    }
};
