<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_type'); // student | personnel
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('purpose')->nullable();
            $table->timestamp('visited_at')->useCurrent();
            $table->timestamps();

            $table->index('student_id');
            $table->index('personnel_id');
            $table->index('visited_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_visits');
    }
};
