<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('role_code', 255)->unique();
            $table->string('role_name', 255);
            $table->text('description')->nullable();
        });
        // create role 'mahasiswa', 'operator', dosen_wali', 'departemen'
        DB::table('roles')->insert([
            ['role_code' => 'mahasiswa', 'role_name' => 'Mahasiswa', 'description' => 'Mahasiswa'],
            ['role_code' => 'operator', 'role_name' => 'Operator', 'description' => 'Operator'],
            ['role_code' => 'dosen_wali', 'role_name' => 'Dosen Wali', 'description' => 'Dosen'],
            ['role_code' => 'departemen', 'role_name' => 'Departemen', 'description' => 'Departemen'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
