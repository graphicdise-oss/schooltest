<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->boolean('email_sent')->default(false);
            $table->timestamps();

            $table->index('personnel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
