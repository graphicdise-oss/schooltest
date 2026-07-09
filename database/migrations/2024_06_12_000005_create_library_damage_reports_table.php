<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_damage_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('รอดำเนินการ'); // รอดำเนินการ / ซ่อมแล้ว / จำหน่ายออก
            $table->date('reported_at');
            $table->date('resolved_at')->nullable();
            $table->timestamps();

            $table->index('book_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_damage_reports');
    }
};
