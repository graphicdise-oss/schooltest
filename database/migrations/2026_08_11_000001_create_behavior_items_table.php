<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_items', function (Blueprint $table) {
            $table->id('behavior_item_id');
            $table->string('type'); // 'add' = เพิ่มคะแนน, 'deduct' = ลดคะแนน
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->decimal('points', 5, 2)->default(0); // แต้ม (ค่าบวกเสมอ ทิศทางกำหนดโดย type)
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_items');
    }
};
