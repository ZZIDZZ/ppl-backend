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
        Schema::table('irs', function (Blueprint $table) {
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
        });
        Schema::table('khs', function (Blueprint $table) {
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
        });
        Schema::table('pkl', function (Blueprint $table) {
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
        });
        Schema::table('skripsi', function (Blueprint $table) {
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skripsi', function (Blueprint $table) {
            $table->dropColumn('semester_akademik_id');
        });
        Schema::table('pkl', function (Blueprint $table) {
            $table->dropColumn('semester_akademik_id');
        });
        Schema::table('khs', function (Blueprint $table) {
            $table->dropColumn('semester_akademik_id');
        });
        Schema::table('irs', function (Blueprint $table) {
            $table->dropColumn('semester_akademik_id');
        });
    }
};
