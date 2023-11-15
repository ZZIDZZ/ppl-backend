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
        Schema::create('pkl', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->float('nilai', 16, 2)->nullable()->default(null)->change();
            $table->boolean('is_selesai')->default(false);
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_lulus')->nullable();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->foreignId('irs_id')->nullable()->constrained('irs');
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
            $table->text('file_pkl')->nullable();
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
        Schema::dropIfExists('pkl');
    }
};
