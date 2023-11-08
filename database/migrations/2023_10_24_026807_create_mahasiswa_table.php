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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('dosen_wali_id')->constrained('dosen_wali');
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('nim')->unique();
            $table->string('email')->nullable();
            $table->bigInteger('tahun_masuk')->nullable();
            $table->boolean('password_changed')->default(false)->comment('0: Butuh Ganti Password, 1: Password Sudah Diganti');
            $table->string('jalur_masuk')->nullable();
            $table->string('status')->nullable()->default('Aktif')->comment('Aktif, Cuti, Mangkir, DO, Undur Diri, Lulus, dan Meninggal Dunia');
            $table->timestampsTz($precision = 0);

        });
        DB::table('mahasiswa')->insert([
            'user_id' => 1,
            'dosen_wali_id' => 1,
            'name' => 'Nahida',
            'phone_number' => '08123456789',
            'nim' => '24060121130051',
            'email' => 'mahasiswa@gmail.com',
            'jalur_masuk' => 'SNMPTN',
            'tahun_masuk' => '2021',
            'status' => 'Aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
};
