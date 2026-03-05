<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_kb_files', function (Blueprint $table) {
            $table->id();

            $table->string('scope', 20)->index(); // global|course
            $table->unsignedBigInteger('course_id')->nullable()->index();

            $table->unsignedBigInteger('ai_kb_entry_id')->nullable()->index();

            $table->string('original_name');
            $table->string('stored_path'); // local storage path
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            // OpenAI IDs
            $table->string('openai_file_id')->nullable()->index();
            $table->string('openai_vector_store_id')->nullable()->index();

            // pending|uploaded|indexed|failed
            $table->string('status', 20)->default('pending')->index();
            $table->longText('last_error')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // If you want foreign keys:
            // $table->foreign('ai_kb_entry_id')->references('id')->on('ai_kb_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_kb_files');
    }
};