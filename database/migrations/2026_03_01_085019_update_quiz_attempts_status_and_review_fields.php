<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Convert status to VARCHAR so you can store: in_progress/submitted/reviewed/graded
        DB::statement("ALTER TABLE quiz_attempts MODIFY status VARCHAR(20) NOT NULL DEFAULT 'in_progress'");

        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_attempts', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('quiz_attempts', 'graded_at')) {
                $table->timestamp('graded_at')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_attempts', 'graded_at')) {
                $table->dropColumn('graded_at');
            }
            if (Schema::hasColumn('quiz_attempts', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });

        // Keep it varchar on rollback (safe). If you REALLY want enum back, you must write enum SQL here.
    }
};