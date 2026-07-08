<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_documents', function (Blueprint $table) {
            $table->string('kind')->default('file')->after('id'); // file = ไฟล์ดาวน์โหลด, image = รูปในคำชี้แจง
        });
    }

    public function down(): void
    {
        Schema::table('admission_documents', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
