<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_attempt_answers', 'answer_json')) {
                $table->json('answer_json')->nullable()->after('question_id');
            }

            if (!Schema::hasColumn('quiz_attempt_answers', 'file_path')) {
                $table->string('file_path')->nullable()->after('answer_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_attempt_answers', 'answer_json')) {
                $table->dropColumn('answer_json');
            }
            if (Schema::hasColumn('quiz_attempt_answers', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
};