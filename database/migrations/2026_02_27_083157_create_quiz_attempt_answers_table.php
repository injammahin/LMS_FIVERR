<?php

// database/migrations/xxxx_xx_xx_create_quiz_attempt_answers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();

            // for choice answers store option IDs
            $table->json('selected_option_ids')->nullable();

            // for text answer
            $table->longText('text_answer')->nullable();

            // for file answer
            $table->string('file_path')->nullable();

            $table->boolean('is_correct')->nullable();
            $table->integer('marks_awarded')->default(0);

            $table->timestamps();

            $table->unique(['attempt_id','question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};