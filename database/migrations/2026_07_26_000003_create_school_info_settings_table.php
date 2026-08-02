<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_info_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name', 255)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('fax', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamps();
        });

        // ตั้งค่าเริ่มต้นจากข้อมูลที่มีอยู่แล้วในไฟล์รายงานเดิมของโรงเรียน แก้ไขภายหลังได้
        DB::table('school_info_settings')->insert([
            'school_name' => 'โรงเรียนสาธิตมหาวิทยาลัยราชภัฏวไลยอลงกรณ์ ในพระบรมราชูปถัมภ์',
            'phone' => '02-529-0864',
            'fax' => '02-529-0864',
            'website' => 'https://satit.vru.ac.th',
            'email' => 'satit@vru.ac.th, Adimsatit_school@satitvru.ac.th',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_info_settings');
    }
};
