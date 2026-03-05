<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_vector_stores', function (Blueprint $table) {
            $table->id();

            // global | course
            $table->string('scope', 20)->index();
            $table->unsignedBigInteger('course_id')->nullable()->index();

            $table->string('name');
            $table->string('openai_vector_store_id')->unique();
            $table->string('status')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            // If you have courses table:
            // $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_vector_stores');
    }
};