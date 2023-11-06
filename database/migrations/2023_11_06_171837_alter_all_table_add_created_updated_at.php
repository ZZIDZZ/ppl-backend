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
        $schemas = [
            'khs',
            'irs',
            'pkl',
            'skripsi',
            'dosen_wali',
            'mahasiswa',
            'riwayat_status_akademik',
            'semester_akademik',
            'users',
            'departemen',
            'operator_departemen',
            'permissions',
            'roles',
        ];
        foreach($schemas as $schema) {
            Schema::table($schema, function (Blueprint $table) {
                $table->timestampsTz($precision = 0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schemas = [
            'khs',
            'irs',
            'pkl',
            'skripsi',
            'dosen_wali',
            'mahasiswa',
            'riwayat_status_akademik',
            'semester_akademik',
            'users',
            'departemen',
            'operator_departemen',
            'permissions',
            'roles',
        ];
        foreach($schemas as $schema) {
            Schema::table($schema, function (Blueprint $table) {
                $table->dropTimestampsTz();
            });
        }
    }
};
