<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatStatusAkademik extends Model
{
    use HasFactory;
    protected $table = 'riwayat_status_akademik';
    public $timestamps = false;

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
        'mahasiswa_id' => 'required',
        'semester_akademik_id' => 'required',
    ];

    const FIELD_DEFAULT_VALUE = [
    ];

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "mahasiswa_id" => [
            "operator" => "=",
        ],
        "semester_akademik_id" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'mahasiswa_id',
        'semester_akademik_id',
    ];

    public static function beforeInsert($input)
    {
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
