<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_type_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('leave_quota_groups')->cascadeOnDelete();
            $table->string('leave_type_key', 50);
            $table->string('leave_type_name', 100);
            $table->smallInteger('days_per_year');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_type_quotas');
    }
};
