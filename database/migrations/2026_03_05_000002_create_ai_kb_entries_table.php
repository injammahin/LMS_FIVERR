<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_kb_entries', function (Blueprint $table) {
            $table->id();

            // global | course
            $table->string('scope', 20)->index();
            $table->unsignedBigInteger('course_id')->nullable()->index();

            // qa | doc
            $table->string('type', 20)->default('doc')->index();

            $table->string('title');
            $table->longText('question')->nullable(); // for QA
            $table->longText('answer')->nullable();   // for QA
            $table->longText('body')->nullable();     // for DOC
            $table->string('keywords')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_kb_entries');
    }
};