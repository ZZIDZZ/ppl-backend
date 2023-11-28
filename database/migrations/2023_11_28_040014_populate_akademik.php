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
        // insert to irs with mahasiswa_id = 1
        DB::table('irs')->insert([[
            'sks_semester' => 24,
            'mahasiswa_id' => 1,
            'semester' => 1,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],[
            'sks_semester' => 21,
            'mahasiswa_id' => 1,
            'semester' => 2,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],[
            'sks_semester' => 18,
            'mahasiswa_id' => 1,
            'semester' => 3,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],[
            'sks_semester' => 22,
            'mahasiswa_id' => 1,
            'semester' => 4,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],[
            'sks_semester' => 22,
            'mahasiswa_id' => 1,
            'semester' => 5,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],[
            'sks_semester' => 22,
            'mahasiswa_id' => 1,
            'semester' => 6,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'sks_semester' => 22,
            'mahasiswa_id' => 1,
            'semester' => 7,
            'file_scan_irs' => '',
            'status_code' => 'waiting_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    // insert to khs with mahasiswa_id = 1
    DB::table('khs')->insert([[
        'mahasiswa_id' => 1,
        'semester' => 1,
        'ip_semester' => 3.5,
        'created_at' => now(),
        'updated_at' => now(),
    ],[
        'mahasiswa_id' => 1,
        'semester' => 2,
        'ip_semester' => 3.9,
        'created_at' => now(),
        'updated_at' => now(),
    ],[
        'mahasiswa_id' => 1,
        'semester' => 3,
        'ip_semester' => 3.7,
        'created_at' => now(),
        'updated_at' => now(),
    ],[
        'mahasiswa_id' => 1,
        'semester' => 4,
        'ip_semester' => 3.96,
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    // insert to pkl with mahasiswa_id = 1
    DB::table('pkl')->insert([[
        'mahasiswa_id' => 1,
        'semester' => 5,
        'nilai' => 80,
        'created_at' => now(),
        'updated_at' => now(),
    ]]);

    // insert to skripsi with mahasiswa_id = 1
    DB::table('skripsi')->insert([[
        'mahasiswa_id' => 1,
        'semester' => 7,
        'nilai' => 80,
        'created_at' => now(),
        'updated_at' => now(),
    ]]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
