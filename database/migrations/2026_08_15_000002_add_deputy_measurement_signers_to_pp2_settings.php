<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pp2_settings', 'deputy_academic_name')) {
                $table->string('deputy_academic_name')->nullable()->after('director_personnel_id');
            }
            if (!Schema::hasColumn('pp2_settings', 'deputy_academic_personnel_id')) {
                $table->unsignedBigInteger('deputy_academic_personnel_id')->nullable()->after('deputy_academic_name');
            }
            if (!Schema::hasColumn('pp2_settings', 'measurement_head_name')) {
                $table->string('measurement_head_name')->nullable()->after('deputy_academic_personnel_id');
            }
            if (!Schema::hasColumn('pp2_settings', 'measurement_head_personnel_id')) {
                $table->unsignedBigInteger('measurement_head_personnel_id')->nullable()->after('measurement_head_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            $table->dropColumn(['deputy_academic_name', 'deputy_academic_personnel_id', 'measurement_head_name', 'measurement_head_personnel_id']);
        });
    }
};
