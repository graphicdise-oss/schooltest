<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('score_categories') && !Schema::hasColumn('score_categories', 'is_checkbox')) {
            Schema::table('score_categories', function (Blueprint $table) {
                $table->boolean('is_checkbox')->default(false)->after('sort_order');
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('score_categories') && Schema::hasColumn('score_categories', 'is_checkbox')) {
            Schema::table('score_categories', function (Blueprint $table) {
                $table->dropColumn('is_checkbox');
            });
        }
    }
};
