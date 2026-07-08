<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id')->nullable(); // ระเบียบการของระดับชั้นไหน (null = ทั่วไป)
            $table->string('title');                            // ชื่อไฟล์/หัวข้อ
            $table->string('file_path');                        // path ในดิสก์ public
            $table->timestamps();

            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_documents');
    }
};
