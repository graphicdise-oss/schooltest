<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_doc_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedInteger('semester_id');
            $table->unsignedInteger('level_id');
            $table->string('doc_set', 20)->nullable();
            $table->string('doc_number', 20)->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_doc_numbers');
    }
};
