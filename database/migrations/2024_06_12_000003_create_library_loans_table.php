<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string('borrower_type'); // student | personnel
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->date('borrowed_at');
            $table->date('due_at');
            $table->date('returned_at')->nullable();
            $table->string('status')->default('ยืมอยู่'); // ยืมอยู่ / คืนแล้ว / ชำรุด / สูญหาย
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('book_id');
            $table->index('student_id');
            $table->index('personnel_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_loans');
    }
};
