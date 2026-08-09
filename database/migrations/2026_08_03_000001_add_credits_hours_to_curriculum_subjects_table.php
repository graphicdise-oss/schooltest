<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->decimal('credits', 4, 1)->nullable()->after('subject_id');
            $table->unsignedInteger('hours_per_year')->nullable()->after('credits');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->dropColumn(['credits', 'hours_per_year']);
        });
    }
};
