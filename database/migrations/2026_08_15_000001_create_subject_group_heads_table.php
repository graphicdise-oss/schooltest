<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_group_heads', function (Blueprint $table) {
            $table->id();
            $table->string('subject_group', 255)->unique();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('head_name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_group_heads');
    }
};
