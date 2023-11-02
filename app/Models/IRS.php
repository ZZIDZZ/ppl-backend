<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IRS extends Model
{

    // Schema::create('irs', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->float('sks_semester', 16, 2)->default(0);
    //     $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
    //     $table->foreignId('riwayat_status_akademik_id')->nullable()->constrained('riwayat_status_akademik');
    //     $table->text('file_scan_irs')->nullable();
    // });
    use HasFactory;

    const TABLE = 'irs';

    const TITLE = 'Isian Rencana Studi';
    
}
