<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('assignments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('course_id')->constrained()->cascadeOnDelete();

      $table->string('title');
      $table->longText('description')->nullable(); // Quill HTML
      $table->string('attachment')->nullable();    // optional teacher attachment

      $table->enum('submission_type', ['text', 'file', 'text_file'])->default('file');
      $table->enum('grading_type', ['points', 'pass_fail'])->default('points');

      $table->unsignedInteger('total_marks')->nullable(); // required if points
      $table->unsignedInteger('max_attempts')->nullable(); // null = unlimited

      $table->dateTime('due_at')->nullable();
      $table->boolean('allow_late')->default(false);
      $table->dateTime('late_until')->nullable();

      $table->enum('status', ['draft', 'published'])->default('draft');
      $table->timestamps();

      $table->index(['course_id', 'status']);
      $table->index(['course_id', 'due_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('assignments');
  }
};