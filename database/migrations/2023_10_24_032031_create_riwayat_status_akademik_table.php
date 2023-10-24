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
        Schema::create('riwayat_status_akademik', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->foreignId('mahasiswa_id')->nullable()->constrained('mahasiswa');
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
            $table->boolean('active')->nullable()->default(true);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestampsTz($precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('riwayat_status_akademik');
    }
};
