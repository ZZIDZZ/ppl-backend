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

        // add semester to irs
        Schema::table("irs", function (Blueprint $table) {
            $table->bigInteger('semester')->nullable();
            // index semester
            $table->index('semester');
        });

        Schema::table("khs", function (Blueprint $table) {
            $table->bigInteger('semester')->nullable();
            // index semester
            $table->index('semester');
        });

        Schema::table("pkl", function (Blueprint $table) {
            $table->dropColumn("is_selesai");
            $table->dropColumn("is_lulus");
            $table->dropColumn("tanggal_selesai");
            $table->bigInteger('semester')->nullable();
            // index semester
            $table->index('semester');
        });

        Schema::table("skripsi", function (Blueprint $table) {
            $table->dropColumn("is_lulus");
            $table->dropColumn("is_selesai");
            $table->dropColumn("tanggal_selesai");
            $table->bigInteger('semester')->nullable();
            // index semester
            $table->index('semester');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("irs", function (Blueprint $table) {
            $table->dropColumn("semester");
        });

        Schema::table("khs", function (Blueprint $table) {
            $table->dropColumn("semester");
        });

        Schema::table("pkl", function (Blueprint $table) {
            $table->boolean("is_selesai")->default(false);
            $table->date("tanggal_selesai")->nullable();
            $table->dropColumn("semester");
        });

        Schema::table("skripsi", function (Blueprint $table) {
            $table->boolean("is_selesai")->default(false);
            $table->date("tanggal_selesai")->nullable();
            $table->dropColumn("semester");
        });
    }
};
