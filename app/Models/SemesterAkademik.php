<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterAkademik extends Model
{
    use HasFactory;
    protected $table = 'semester_akademik';

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

    const FIELD_DEFAULT_VALUES = [
    ];

    protected $fillable = [
        'tahun_ajaran',
        'semester',
    ];
}
