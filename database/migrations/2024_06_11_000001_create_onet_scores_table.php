<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onet_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('year_id');   // ปีการศึกษาที่สอบ
            $table->unsignedBigInteger('level_id')->nullable(); // ระดับชั้นที่สอบ (ป.6/ม.3/ม.6)
            $table->string('subject');               // ภาษาไทย / คณิตศาสตร์ / วิทยาศาสตร์ / ภาษาอังกฤษ
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'year_id', 'subject']);
            $table->index('year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onet_scores');
    }
};
