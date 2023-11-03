<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;
    protected $table = 'mahasiswa';
    public $timestamps = false;

    // Schema::create('mahasiswa', function (Blueprint $table) {
    //     $table->bigIncrements('id')->unsigned();
    //     $table->foreignId('user_id')->nullable()->constrained('users');
    //     $table->foreignId('dosen_wali_id')->nullable()->constrained('dosen_wali');
    //     $table->string('name')->nullable();
    //     $table->string('phone_number')->nullable();
    //     $table->string('nim')->nullable();
    // });

    const TABLE = 'mahasiswa';
    const TITLE = 'Mahasiswa';
    
    const FIELDS = [
        'id',
        'user_id',
        'dosen_wali_id',
        'name',
        'phone_number',
        'nim',
    ];

    const FIELD_TYPES = [
        'id' => 'primary_key',
        'user_id' => 'foreign_key',
        'name' => 'string',
        'phone_number' => 'string',
        'nim' => 'string',
    ];

    const FIELD_INPUT = [
        'user_id',
        'dosen_wali_id',
        'name',
        'phone_number',
        'nim',
    ];

    const FIELD_SORTABLE = [
        'id',
        'user_id',
        'dosen_wali_id',
        'name',
        'phone_number',
        'nim',
    ];

    const FIELD_SEARCHABLE = [
        'name',
        'phone_number',
        'nim',
    ];

    const FIELD_ALIAS = [
        'id' => 'id',
        'user_id' => 'id user',
        'dosen_wali_id' => 'id dosen wali',
        'name' => 'Nama',
        'phone_number' => 'Nomor Telepon',
        'nim' => 'NIM',
    ];

    const FIELD_RELATIONS = [
        'user_id' => [
            'linkTable' => 'users',
            'aliasTable' => 'A',
            'linkField' => 'id',
            'displayName' => 'email',
            'selectFields' => ['email'],
            'selectValue' => ['email'],
        ],
        'dosen_wali_id' => [
            'linkTable' => 'dosen_wali',
            'aliasTable' => 'B',
            'linkField' => 'id',
            'displayName' => 'dosen_wali',
            'selectFields' => ['name'],
            'selectValue' => ['name'],
        ],
    ];

    const FIELD_VALIDATION = [
        'user_id' => 'required',
        'name' => 'required',
        'phone_number' => 'nullable',
        'nim' => 'nullable',
    ];

    const FIELD_DEFAULT_VALUE = [
        'user_id' => '',
        'name' => '',
        'phone_number' => '',
        'nim' => '',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'nim',
    ];
}
