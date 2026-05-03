<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type', 50);
            $table->smallInteger('alert_number');
            $table->smallInteger('threshold_value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_notification_settings');
    }
};
