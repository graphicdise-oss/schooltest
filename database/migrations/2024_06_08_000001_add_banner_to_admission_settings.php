<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_settings', function (Blueprint $table) {
            $table->string('banner_image')->nullable()->after('instructions'); // รูปแบนเนอร์หน้าประชาสัมพันธ์
            $table->text('required_docs')->nullable()->after('banner_image');   // หลักฐานที่ต้องเตรียม
        });
    }

    public function down(): void
    {
        Schema::table('admission_settings', function (Blueprint $table) {
            $table->dropColumn(['banner_image', 'required_docs']);
        });
    }
};
