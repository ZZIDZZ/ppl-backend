<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;
    protected $table = 'departemen';

    const TABLE = 'departemen';
    const TITLE = 'Departemen';
    
    const FIELDS = [
        'id',
        'user_id',
        'name',
        'phone_number',
    ];

    const FIELD_TYPES = [
        'id' => 'primary_key',
        'user_id' => 'foreign_key',
        'name' => 'string',
        'phone_number' => 'string',
    ];

    const FIELD_INPUT = [
        'user_id',
        'name',
        'phone_number',
    ];

    const FIELD_SORTABLE = [
        'id',
        'user_id',
        'name',
        'phone_number',
    ];

    const FIELD_SEARCHABLE = [
        'name',
        'phone_number',
    ];

    const FIELD_ALIAS = [
        'id' => 'id',
        'user_id' => 'id user',
        'name' => 'Nama',
        'phone_number' => 'Nomor Telepon',
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
    ];

    const FIELD_VALIDATION = [
        'user_id' => 'required',
        'name' => 'required',
        'phone_number' => 'nullable',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
    ];
}
