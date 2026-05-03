<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('position_name', 100)->nullable();
            $table->string('personnel_name', 100);
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_notification_recipients');
    }
};
