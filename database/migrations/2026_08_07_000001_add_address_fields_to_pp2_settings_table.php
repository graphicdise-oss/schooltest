<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pp2_settings', 'tambon')) {
                $table->string('tambon', 100)->nullable()->after('affiliation');
            }
            if (!Schema::hasColumn('pp2_settings', 'amphoe')) {
                $table->string('amphoe', 100)->nullable()->after('tambon');
            }
            if (!Schema::hasColumn('pp2_settings', 'education_area')) {
                $table->string('education_area', 255)->nullable()->after('province');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pp2_settings', function (Blueprint $table) {
            $table->dropColumn(['tambon', 'amphoe', 'education_area']);
        });
    }
};
