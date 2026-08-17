<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_types', 'abbr_th')) {
                $table->string('abbr_th')->nullable()->after('leave_type_name');
            }
            if (! Schema::hasColumn('leave_types', 'name_en')) {
                $table->string('name_en')->nullable()->after('abbr_th');
            }
            if (! Schema::hasColumn('leave_types', 'abbr_en')) {
                $table->string('abbr_en')->nullable()->after('name_en');
            }
            if (! Schema::hasColumn('leave_types', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('abbr_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            foreach (['abbr_th', 'name_en', 'abbr_en', 'sort_order'] as $col) {
                if (Schema::hasColumn('leave_types', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
