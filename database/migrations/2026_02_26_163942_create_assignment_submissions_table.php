<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('assignment_submissions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student

      $table->longText('submission_text')->nullable();
      $table->string('submission_file')->nullable();

      $table->dateTime('submitted_at')->nullable();
      $table->enum('status', ['draft', 'submitted', 'graded'])->default('submitted');

      // grading
      $table->unsignedInteger('marks_awarded')->nullable();
      $table->boolean('is_passed')->nullable(); // pass_fail grading
      $table->longText('feedback')->nullable();
      $table->string('feedback_file')->nullable(); // teacher returns file

      $table->timestamps();

      $table->unique(['assignment_id', 'user_id']); // 1 active submission per student (simpler)
      $table->index(['assignment_id', 'status']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('assignment_submissions');
  }
};