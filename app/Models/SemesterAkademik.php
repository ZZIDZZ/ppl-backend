<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterAkademik extends Model
{
    use HasFactory;
    protected $table = 'semester_akademik';
    public $timestamps = true;

    // Schema::create('semester_akademik', function (Blueprint $table) {
    //     $table->bigIncrements('id')->unsigned();
    //     $table->bigInteger('tahun_ajaran')->nullable();
    //     $table->bigInteger('semester')->nullable()->comment('1: Ganjil, 2: Genap');
    // });

    const TABLE = 'semester_akademik';
    const TITLE = 'Semester Akademik';
    
    const FIELDS = [
        'id',
        'tahun_ajaran',
        'semester',
    ];

    const FIELD_TYPES = [
        'id' => 'primary_key',
        'tahun_ajaran' => 'integer',
        'semester' => 'integer',
    ];

    const FIELD_INPUT = [
        'tahun_ajaran',
        'semester',
    ];

    const FIELD_SORTABLE = [
        'id',
        'tahun_ajaran',
        'semester',
    ];

    const FIELD_SEARCHABLE = [
        'tahun_ajaran',
        'semester',
    ];

    const FIELD_ALIAS = [
        'id' => 'id',
        'tahun_ajaran' => 'Tahun Ajaran',
        'semester' => 'Semester',
    ];

    const FIELD_RELATIONS = [
    ];

    const FIELD_VALIDATION = [
        'tahun_ajaran' => 'required',
        'semester' => 'required',
    ];

    const FIELD_DEFAULT_VALUE = [
    ];

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "tahun_ajaran" => [
            "operator" => ">=",
        ],
        "semester" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'tahun_ajaran',
        'semester',
    ];

    public static function beforeInsert($input)
    {
        // check semester_akademik with same tahun_ajaran and semester has been made before
        $semester_akademik = SemesterAkademik::where('tahun_ajaran', $input['tahun_ajaran'])->where('semester', $input['semester'])->first();
        if($semester_akademik){
            throw new \Exception("Semester Akademik dengan tahun ajaran " . $input['tahun_ajaran'] . " dan semester " . $input['semester'] . " sudah ada");
        }
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
