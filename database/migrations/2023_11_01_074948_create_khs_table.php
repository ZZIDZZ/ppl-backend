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
        Schema::create('khs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('ip_semester', 16, 2)->default(0);
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->foreignId('riwayat_status_akademik_id')->nullable()->constrained('riwayat_status_akademik');
            $table->text('file_scan_irs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('khs');
    }
};
