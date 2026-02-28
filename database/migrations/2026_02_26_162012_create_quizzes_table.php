<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->longText('description')->nullable();

            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('pass_mark')->nullable(); // percent (0-100)
            $table->unsignedInteger('max_attempts')->nullable();

            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);

            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};