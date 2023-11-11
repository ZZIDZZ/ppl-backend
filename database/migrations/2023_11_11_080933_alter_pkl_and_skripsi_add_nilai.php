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
        Schema::table('pkl', function (Blueprint $table) {
            $table->float('nilai', 16, 2)->nullable()->default(null);
        });
        Schema::table('skripsi', function (Blueprint $table) {
            $table->float('nilai', 16, 2)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(['pkl', 'skripsi'], function (Blueprint $table) {
            $table->dropColumn('nilai');
        });
    }
};
