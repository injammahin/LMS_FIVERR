<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();

            $table->unique(['subject_id', 'slug']); // unique per subject
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};