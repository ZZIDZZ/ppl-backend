<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KHS extends Model
{

    // Schema::create('khs', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->float('ip_semester', 16, 2)->default(0);
    //     $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
    //     $table->foreignId('riwayat_status_akademik_id')->nullable()->constrained('riwayat_status_akademik');
    //     $table->text('file_scan_khs')->nullable();
    //     $table->string('status_code')->nullable()->comment('waiting_approval / approved');
    // });
    use HasFactory;

    const TABLE = 'khs';

    const TITLE = 'Kartu Hasil Studi';
    protected $table = 'khs';
    public $timestamps = false;

    const FIELDS = [
        'id',
        'ip_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_khs',
        'status_code',
    ];
    const FIELD_TYPES = [
        // 'id',
        // 'ip_semester',
        // 'mahasiswa_id',
        // 'riwayat_status_akademik_id',
        // 'file_scan_khs',
        // 'status_code',
    ];
    const FIELD_INPUT = [
        'ip_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_khs',
        'status_code',
    ];
    const FIELD_SORTABLE = [
        'id',
        'ip_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_khs',
        'status_code',
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_scan_khs',
        'status_code',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'ip_semester' => 'ip semester',
        'mahasiswa_id' => 'id mahasiswa',
        'riwayat_status_akademik_id' => 'id riwayat status akademik',
        'file_scan_khs' => 'file scan khs',
        'status_code' => 'kode status',
    ];
    const FIELD_RELATIONS = [
        'mahasiswa_id' => [
            'linkTable' => 'mahasiswa',
            'aliasTable' => 'A',
            'linkField' => 'id',
            'displayName' => 'name',
            'selectFields' => ['name'],
            'selectValue' => ['name'],
        ],
        'riwayat_status_akademik_id' => [
            'linkTable' => 'riwayat_status_akademik',
            'aliasTable' => 'B',
            'linkField' => 'id',
            'displayName' => 'riwayat_status_akademik',
            'selectFields' => ['semester_akademik_id'],
            'selectValue' => ['semester_akademik_id'],
        ],
    ];

    const FIELD_VALIDATION = [
        'ip_semester' => 'nullable',
        'mahasiswa_id' => 'required',
        'riwayat_status_akademik_id' => 'nullable',
        'file_scan_irs' => 'nullable',
        'status_code' => 'nullable',
    ];

    const FIELD_DEFAULT_VALUE = [
        'ip_semester' => 0,
        'mahasiswa_id' => '',
        'riwayat_status_akademik_id' => '',
        'file_scan_irs' => '',
        'status_code' => '',

    ];

    protected $fillable = [
        'ip_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_khs',
        'status_code',
    ];
}
