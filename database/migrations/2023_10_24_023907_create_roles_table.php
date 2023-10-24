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
            $table->boolean('active')->nullable()->default(true);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestampsTz($precision = 0);
        });
        // create role 'mahasiswa', 'operator', dosen_wali', 'departemen'
        DB::table('roles')->insert([
            ['role_code' => 'mahasiswa', 'role_name' => 'Mahasiswa', 'description' => 'Mahasiswa', 'active' => true, 'created_by' => 1, 'updated_by' => 1],
            ['role_code' => 'operator', 'role_name' => 'Operator', 'description' => 'Operator', 'active' => true, 'created_by' => 1, 'updated_by' => 1],
            ['role_code' => 'dosen_wali', 'role_name' => 'Dosen Wali', 'description' => 'Dosen', 'active' => true, 'created_by' => 1, 'updated_by' => 1],
            ['role_code' => 'departemen', 'role_name' => 'Departemen', 'description' => 'Departemen', 'active' => true, 'created_by' => 1, 'updated_by' => 1],
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
