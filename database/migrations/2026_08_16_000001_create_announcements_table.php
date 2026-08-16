<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id('announcement_id');
            $table->string('title');
            $table->string('message_type')->nullable();     // เช่น แจ้งประกาศ/กิจกรรม, ข่าวสาร
            $table->string('send_type')->default('ส่งทันที');
            $table->boolean('requires_ack')->default(false); // ต้องกด "รับทราบ" หรือไม่
            $table->string('delivery_mode')->default('รายกลุ่ม');
            $table->string('target_type')->default('all');   // all | level | section
            $table->unsignedBigInteger('target_level_id')->nullable();
            $table->unsignedBigInteger('target_section_id')->nullable();
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'student_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_recipients');
        Schema::dropIfExists('announcements');
    }
};
