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
            $table->text('file_scan_khs')->nullable();
            $table->string('status_code')->default('waiting_approval')->comment('waiting_approval, approved');
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
        Schema::dropIfExists('khs');
    }
};
