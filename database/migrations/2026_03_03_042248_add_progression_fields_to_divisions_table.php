<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('divisions', function (Blueprint $table) {
        $table->integer('level')->default(1)->after('name');
        $table->integer('promotion_percent')->default(70)->after('level');
        $table->boolean('auto_promote')->default(true)->after('promotion_percent');
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('divisions', function (Blueprint $table) {
            //
        });
    }
};
