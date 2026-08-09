<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->string('study_plan', 100)->nullable()->after('section_number');
        });
    }

    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropColumn('study_plan');
        });
    }
};
