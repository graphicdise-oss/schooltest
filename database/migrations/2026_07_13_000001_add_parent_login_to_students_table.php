<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('parent_password')->nullable()->after('id_card_number');
            $table->boolean('parent_password_changed')->default(false)->after('parent_password');
        });

        // ตั้งรหัสผ่านผู้ปกครองเริ่มต้น = เลขบัตรประชาชนนักเรียน ให้กับนักเรียนที่มีอยู่แล้วในระบบ
        DB::table('students')->whereNotNull('id_card_number')->orderBy('student_id')
            ->chunkById(200, function ($students) {
                foreach ($students as $s) {
                    DB::table('students')->where('student_id', $s->student_id)->update([
                        'parent_password' => Hash::make($s->id_card_number),
                    ]);
                }
            }, 'student_id');
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['parent_password', 'parent_password_changed']);
        });
    }
};
