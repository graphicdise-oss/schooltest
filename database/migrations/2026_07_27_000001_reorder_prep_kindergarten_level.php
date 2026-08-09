<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $minOrder = DB::table('levels')->where('name', '!=', 'เตรียมอนุบาล')->min('sort_order') ?? 1;

        DB::table('levels')
            ->where('name', 'เตรียมอนุบาล')
            ->update(['sort_order' => $minOrder - 1]);
    }

    public function down(): void
    {
        // ไม่มีค่าดั้งเดิมให้ย้อนกลับได้อย่างปลอดภัย ปล่อยว่างไว้ตั้งใจ
    }
};
