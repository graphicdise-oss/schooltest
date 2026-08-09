<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->decimal('hours_per_week', 4, 1)->nullable()->after('hours_per_year');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_subjects', function (Blueprint $table) {
            $table->dropColumn(['hours_per_week']);
        });
    }
};
