<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pp2_settings', 'school_name')) {
                $table->string('school_name', 255)->nullable();
            }
            if (!Schema::hasColumn('pp2_settings', 'province')) {
                $table->string('province', 100)->nullable();
            }
            if (!Schema::hasColumn('pp2_settings', 'affiliation')) {
                $table->string('affiliation', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'province', 'affiliation']);
        });
    }
};
