<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title')->default('Untitled Note');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->text('excerpt')->nullable();

            $table->boolean('is_pinned')->default(false);
            $table->timestamp('last_saved_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'subject_id']);
            $table->index(['user_id', 'course_id']);
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notes');
    }
};