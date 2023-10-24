<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatStatusAkademik extends Model
{
    use HasFactory;
    protected $table = 'riwayat_status_akademik';

    // Schema::create('riwayat_status_akademik', function (Blueprint $table) {
    //     $table->bigIncrements('id')->unsigned();
    //     $table->foreignId('mahasiswa_id')->nullable()->constrained('mahasiswa');
    //     $table->foreignId('semester_akademik_id')->nullable()->constrained('semester_akademik');
    // });

    const TABLE = 'riwayat_status_akademik';
    const TITLE = 'Riwayat Status Akademik';
    
    const FIELDS = [
        'id',
        'mahasiswa_id',
        'semester_akademik_id',
    ];

    const FIELD_TYPES = [
        'id' => 'primary_key',
        'mahasiswa_id' => 'foreign_key',
        'semester_akademik_id' => 'foreign_key',        
    ];

    const FIELD_INPUT = [
        'mahasiswa_id',
        'semester_akademik_id',
    ];

    const FIELD_SORTABLE = [
        'id',
        'mahasiswa_id',
        'semester_akademik_id',
    ];

    const FIELD_SEARCHABLE = [

    ];

    const FIELD_ALIAS = [
        'id' => 'id',
        'mahasiswa_id' => 'Mahasiswa',
        'semester_akademik_id' => 'Semester Akademik',
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
        'semester_akademik_id' => [
            'linkTable' => 'semester_akademik',
            'aliasTable' => 'B',
            'linkField' => 'id',
            'displayName' => 'semester_akademik',
            'selectFields' => ['tahun_ajaran', 'semester'],
            'selectValue' => ['tahun_ajaran', 'semester'],
        ],
    ];

    const FIELD_VALIDATION = [
        'user_id' => 'required',
        'name' => 'required',
        'phone_number' => 'nullable',
        'nim' => 'nullable',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'nim',
    ];
}
