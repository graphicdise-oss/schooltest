<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pp2_section_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('section_id')->unique();
            $table->date('issued_date')->nullable();
            $table->timestamps();

            $table->foreign('section_id')
                  ->references('section_id')
                  ->on('class_sections')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp2_section_settings');
    }
};
