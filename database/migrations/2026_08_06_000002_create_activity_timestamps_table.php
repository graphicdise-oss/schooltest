<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_timestamps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_id');
            $table->string('personnel_name');
            $table->string('employee_code')->nullable();
            $table->string('route_name');
            $table->string('page_label');
            $table->string('method', 10);
            $table->string('url');
            $table->timestamp('first_recorded_at');
            $table->timestamps();

            $table->unique(['personnel_id', 'route_name']);
            $table->index('personnel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_timestamps');
    }
};
