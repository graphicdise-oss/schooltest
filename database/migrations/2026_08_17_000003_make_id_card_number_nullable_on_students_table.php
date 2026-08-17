<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// โค้ดฝั่งแอปตั้งใจให้ id_card_number เป็นค่าที่ไม่บังคับกรอกมานานแล้ว (ดู StudentController::store/update
// ที่ validate เป็น nullable) แต่ตาราง students (ไม่มี migration สร้างตารางในระบบนี้ เพราะสร้างไว้ก่อน
// แล้วนอกเหนือ Laravel) ยังมี NOT NULL constraint ค้างอยู่ ทำให้บันทึกนักเรียนที่ไม่มีเลขบัตรประชาชนไม่ได้
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('id_card_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('id_card_number')->nullable(false)->change();
        });
    }
};
