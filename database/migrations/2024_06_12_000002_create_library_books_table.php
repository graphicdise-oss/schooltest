<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('code')->nullable()->unique(); // รหัสหนังสือ/เลขทะเบียน
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable();
            $table->unsignedInteger('total_copies')->default(1);     // จำนวนทั้งหมด
            $table->unsignedInteger('available_copies')->default(1); // จำนวนที่ยืมได้ตอนนี้
            $table->string('shelf_location')->nullable();            // ชั้นวาง
            $table->decimal('price', 8, 2)->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
