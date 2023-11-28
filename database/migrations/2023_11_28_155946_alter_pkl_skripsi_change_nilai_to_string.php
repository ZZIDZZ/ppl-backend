<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            // change nilai to string
            $table->string('nilai')->change();
        });

        Schema::table('skripsi', function (Blueprint $table) {
            // change nilai to string
            $table->string('nilai')->change();
        });

        // change all entry in pkl and skripsi to 'A'
        DB::table('pkl')->update(['nilai' => 'A']);
        DB::table('skripsi')->update(['nilai' => 'A']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pkl', function (Blueprint $table) {
            // change nilai to float
            $table->float('nilai', 16, 2)->nullable()->default(null)->change();
        });

        Schema::table('skripsi', function (Blueprint $table) {
            // change nilai to float
            $table->float('nilai', 16, 2)->nullable()->default(null)->change();
        });
    }
};
