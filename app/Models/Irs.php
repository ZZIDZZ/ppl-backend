<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Irs extends Model
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
    public $timestamps = true;
    const TABLE = 'irs';
    const TITLE = 'Isian Rencana Studi';

    const FIELDS = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
        'semester_akademik_id'
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
        'semester_akademik_id'
    ];
    const FIELD_SORTABLE = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
        'semester_akademik_id'
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
        'semester_akademik_id' => 'id semester akademik'
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
        'semester_akademik_id' => [
            'linkTable' => 'semester_akademik',
            'aliasTable' => 'C',
            'linkField' => 'id',
            'displayName' => 'semester_akademik',
            'selectFields' => ['tahun_ajaran', 'semester'],
            'selectValue' => ['tahun_ajaran', 'semester'],
        ],
    ];

    const FIELD_VALIDATION = [
        'sks_semester' => 'nullable',
        'mahasiswa_id' => 'required',
        'riwayat_status_akademik_id' => 'nullable',
        'semester_akademik_id' => 'nullable'
    ];

    const FIELD_DEFAULT_VALUE = [
        'sks_semester' => 0,
        'mahasiswa_id' => '',
        'riwayat_status_akademik_id' => '',
        'file_scan_irs' => '',
    ];

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "sks_semester" => [
            "operator" => "=",
        ],
        "mahasiswa_id" => [
            "operator" => "=",
        ],
        "riwayat_status_akademik_id" => [
            "operator" => "=",
        ],
        "semester_akademik_id" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'sks_semester',
        'mahasiswa_id',
        'riwayat_status_akademik_id',
        'file_scan_irs',
        'semester_akademik_id'
    ];

    public static function beforeInsert($input)
    {
        $riwayat_status_akademik_id = $input['riwayat_status_akademik_id'];
        $semester_akademik_id = RiwayatStatusAkademik::find($riwayat_status_akademik_id)->semester_akademik_id;
        $input['semester_akademik_id'] = $semester_akademik_id;
        return $input;
    }

    public static function afterInsert($object, $input)
    {
        return $object;
    }
    
    public static function beforeUpdate($input)
    {
        return $input;
    }
    
    public static function afterUpdate($object, $input)
    {
        return $object;
    }
    
    public static function beforeDelete($input)
    {
        return $input;
    }

    public static function afterDelete($object, $input)
    {
        return $object;
    }// end custom
}
