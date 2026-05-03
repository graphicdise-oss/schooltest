<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('leave_type_key')->unique(); // sick, personal, vacation, maternity, ordain, abroad
            $table->string('leave_type_name');          // ลาป่วย, ลากิจ, ลาพักร้อน, ...
            $table->integer('days_per_year')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ข้อมูลเริ่มต้น
        DB::table('leave_types')->insert([
            ['leave_type_key' => 'sick',      'leave_type_name' => 'ลาป่วย',         'days_per_year' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['leave_type_key' => 'personal',  'leave_type_name' => 'ลากิจ',          'days_per_year' => 15, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['leave_type_key' => 'vacation',  'leave_type_name' => 'ลาพักร้อน',     'days_per_year' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['leave_type_key' => 'maternity', 'leave_type_name' => 'ลาคลอด',        'days_per_year' => 90, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['leave_type_key' => 'ordain',    'leave_type_name' => 'ลาอุปสมบท',    'days_per_year' => 120, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['leave_type_key' => 'abroad',    'leave_type_name' => 'ลาไปต่างประเทศ', 'days_per_year' => 0,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
