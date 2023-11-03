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
        Schema::create('skripsi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('nilai', 16, 2)->default(0);
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_lulus')->nullable();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->foreignId('riwayat_status_akademik_id')->nullable()->constrained('riwayat_status_akademik');
            $table->text('file_skripsi')->nullable();
            $table->string('status_code')->nullable()->comment('waiting_approval / approved / waiting_approval_over / approved_over');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skripsi');
    }
};
