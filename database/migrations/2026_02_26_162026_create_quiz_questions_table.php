<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();

            $table->enum('type', ['text', 'file', 'single_choice', 'multiple_choice', 'true_false']);
            $table->text('question');
            $table->string('question_image')->nullable();

            $table->longText('explanation')->nullable();
            $table->unsignedInteger('marks')->default(1);
            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->index(['quiz_id', 'type']);
            $table->index(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};