<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_dept_approvers', function (Blueprint $table) {
            $table->id();
            $table->string('department_name', 100);
            $table->string('approver_1', 100)->nullable();
            $table->string('approver_2', 100)->nullable();
            $table->string('approver_3', 100)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_dept_approvers');
    }
};
