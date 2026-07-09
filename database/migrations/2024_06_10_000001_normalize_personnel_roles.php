<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // รวม role เก่า (teacher/staff/viewer) ที่ไม่มีผลต่อสิทธิ์จริง ให้เป็น 'user' เดียว
    // เพื่อให้ตรงกับตัวเลือกในหน้าเว็บ (admin / user) และลดความสับสน
    public function up(): void
    {
        DB::table('personnels')
            ->whereIn('role', ['teacher', 'staff', 'viewer'])
            ->update(['role' => 'user']);
    }

    public function down(): void
    {
        // ย้อนกลับไม่ได้ (ไม่ทราบค่าดั้งเดิมของแต่ละคน) — ปล่อยว่างไว้โดยตั้งใจ
    }
};
