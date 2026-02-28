<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dateTime('ends_at')->nullable()->after('started_at');
            $table->unsignedInteger('duration_seconds')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['ends_at', 'duration_seconds']);
        });
    }
};