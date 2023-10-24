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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable()->constrained('roles');
            $table->rememberToken();
        });
        // create user with role 'mahasiswa', 'operator', dosen', 'departemen'
        DB::table('users')->insert([
            'email' => 'mahasiswa@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 1,
        ]);
        DB::table('users')->insert([
            'email' => 'operator@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 2,
        ]);
        DB::table('users')->insert([
            'email' => 'doswal@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 3,
        ]);
        DB::table('users')->insert([
            'email' => 'departemen@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 4,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
