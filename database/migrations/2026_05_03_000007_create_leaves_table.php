<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_id');
            $table->string('leave_type_key', 50);
            $table->string('leave_type_name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 5, 1)->default(1);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->smallInteger('fiscal_year')->nullable();
            $table->timestamps();

            $table->foreign('personnel_id')->references('personnel_id')->on('personnels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
