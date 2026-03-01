<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // MySQL-safe: convert enum/short string into varchar
        if (Schema::hasTable('quiz_attempts') && Schema::hasColumn('quiz_attempts', 'status')) {
            DB::statement("ALTER TABLE quiz_attempts MODIFY status VARCHAR(20) NOT NULL DEFAULT 'in_progress'");
        }

        // Ensure total exists (you are using total in many places)
        if (Schema::hasTable('quiz_attempts') && !Schema::hasColumn('quiz_attempts', 'total')) {
            DB::statement("ALTER TABLE quiz_attempts ADD total INT NOT NULL DEFAULT 0 AFTER score");
        }
    }

    public function down(): void
    {
        // optional: no down for enum restore
    }
};