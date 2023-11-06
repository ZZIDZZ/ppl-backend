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
    protected $table = 'irs';
    public $timestamp = false;
    const TABLE = 'irs';
    const TITLE = 'Isian Rencana Studi';

    const FIELDS = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
    ];
    const FIELD_TYPES = [
        // 'id' => 'primary_key',
        // 'user_id' => 'foreign_key',
        // 'name' => 'string',
        // 'phone_number' => 'string',
        // 'nim' => 'string',
    ];
    const FIELD_INPUT = [
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
    ];
    const FIELD_SORTABLE = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_scan_irs',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'sks_semester' => 'sks semester',
        'mahasiswa_id' => 'id mahasiswa',
        'riwayat_status_akademik_id' => 'id riwayat status akademik',
        'file_scan_irs' => 'file scan irs',
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
        'sks_semester' => 'nullable',
        'mahasiswa_id' => 'required',
        'riwayat_status_akademik_id' => 'nullable',
        'file_scan_irs' => 'required',
    ];

    const FIELD_DEFAULT_VALUE = [

        'sks_semester' => 0,
        'mahasiswa_id' => '',
        'riwayat_status_akademik_id' => '',
        'file_scan_irs' => '',

    ];

    protected $fillable = [
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
    ];
}
