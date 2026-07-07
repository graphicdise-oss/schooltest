<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pp2_settings', 'registrar_name')) {
                $table->string('registrar_name')->nullable()->after('director_name');
            }
            if (!Schema::hasColumn('pp2_settings', 'registrar_personnel_id')) {
                $table->unsignedBigInteger('registrar_personnel_id')->nullable()->after('registrar_name');
            }
            if (!Schema::hasColumn('pp2_settings', 'director_personnel_id')) {
                $table->unsignedBigInteger('director_personnel_id')->nullable()->after('registrar_personnel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            $table->dropColumn(['registrar_name', 'registrar_personnel_id', 'director_personnel_id']);
        });
    }
};
