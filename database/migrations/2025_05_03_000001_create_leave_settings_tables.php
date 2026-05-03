<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ตั้งค่าทั่วไปของระบบลา
        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('min_approvers')->default(2);
            $table->unsignedTinyInteger('cutoff_day')->default(1);
            $table->unsignedTinyInteger('cutoff_month')->default(10);
            $table->timestamps();
        });

        // รายชื่อผู้อนุมัติตามแผนก
        Schema::create('leave_dept_approvers', function (Blueprint $table) {
            $table->id();
            $table->string('department_name', 100);
            $table->string('approver_1', 100)->nullable();
            $table->string('approver_2', 100)->nullable();
            $table->string('approver_3', 100)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // กลุ่มช่วงปีทำงานสำหรับโควตาการลา
        Schema::create('leave_quota_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('years_from')->default(0);
            $table->unsignedSmallInteger('years_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // โควตาวันลาแต่ละประเภทตามกลุ่มปีทำงาน
        Schema::create('leave_type_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('leave_quota_groups')->cascadeOnDelete();
            $table->string('leave_type_key', 50);
            $table->string('leave_type_name', 100);
            $table->unsignedSmallInteger('days_per_year');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ตั้งค่าการแจ้งเตือน
        Schema::create('leave_notification_settings', function (Blueprint $table) {
            $table->id();
            // notification_type: late_arrival, visa_expiry, work_permit_expiry, license_expiry
            $table->string('notification_type', 50);
            $table->unsignedTinyInteger('alert_number');
            $table->unsignedSmallInteger('threshold_value');
            $table->timestamps();
        });

        // ผู้รับการแจ้งเตือน
        Schema::create('leave_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('position_name', 100)->nullable();
            $table->string('personnel_name', 100);
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_notification_recipients');
        Schema::dropIfExists('leave_notification_settings');
        Schema::dropIfExists('leave_type_quotas');
        Schema::dropIfExists('leave_quota_groups');
        Schema::dropIfExists('leave_dept_approvers');
        Schema::dropIfExists('leave_settings');
    }
};
