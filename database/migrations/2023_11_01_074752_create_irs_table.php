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
        Schema::create('irs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('sks_semester', 16, 2)->default(0);
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
            $table->text('file_scan_irs')->nullable();
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
        Schema::dropIfExists('irs');
    }
};
