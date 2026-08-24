<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ตารางใหม่ทั้งหมด (positions เดิมมีอยู่แล้วในระบบ ไม่แตะ) เก็บสิทธิ์การเข้าถึงเมนูตาม "ตำแหน่ง"
// (ย้ายมาจากที่เคยผูกกับ "ประเภทบุคลากร" — ดู personnel_type_permissions เดิม)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->unsignedBigInteger('position_id');
            $table->string('menu_key');
            $table->string('menu_label')->nullable();
            $table->string('menu_group')->nullable();
            $table->boolean('is_allowed')->default(false);
            $table->timestamps();

            $table->index('position_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_permissions');
    }
};
