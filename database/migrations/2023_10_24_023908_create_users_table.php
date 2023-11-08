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
            $table->string('username')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable()->constrained('roles');
            $table->rememberToken();
            $table->timestampsTz($precision = 0);
        });
        // create user with role 'mahasiswa', 'operator', dosen', 'departemen'
        DB::table('users')->insert([
            'username' => '24060121130051',
            'password' => bcrypt('12345678'),
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'username' => 'operator',
            'password' => bcrypt('12345678'),
            'role_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'username' => '123456789',
            'password' => bcrypt('12345678'),
            'role_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'username' => 'departemen',
            'password' => bcrypt('12345678'),
            'role_id' => 4,
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
        Schema::dropIfExists('users');
    }
};
