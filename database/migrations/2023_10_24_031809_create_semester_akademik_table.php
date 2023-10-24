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
        Schema::create('semester_akademik', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('tahun_ajaran')->nullable();
            $table->bigInteger('semester')->nullable()->comment('1: Ganjil, 2: Genap');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('semester_akademik');
    }
};
