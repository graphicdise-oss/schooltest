<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('leave_type_key');            // อ้างอิง leave_types.leave_type_key
            $table->dateTime('request_date');            // วันที่แจ้งลา
            $table->date('start_date');                  // วันที่เริ่มลา
            $table->date('end_date');                    // วันที่สิ้นสุด
            $table->decimal('num_days', 5, 1)->default(1); // จำนวนวัน
            $table->unsignedBigInteger('requester_id'); // ผู้ยื่นคำร้อง (personnel_id)
            $table->unsignedBigInteger('reviewer_id');  // ผู้ตรวจสอบ (personnel_id)
            $table->string('status')->default('รอการอนุมัติ'); // รอการอนุมัติ / อนุมัติ / ไม่อนุมัติ
            $table->text('reason')->nullable();
            $table->string('attachment')->nullable();
            $table->text('note')->nullable();            // หมายเหตุผู้อนุมัติ
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
