<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_timestamps', function (Blueprint $table) {
            $table->string('target_label')->nullable()->after('page_label');
        });
    }

    public function down(): void
    {
        Schema::table('activity_timestamps', function (Blueprint $table) {
            $table->dropColumn('target_label');
        });
    }
};
