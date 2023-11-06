<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorDepartemen extends Model
{
    use HasFactory;
    protected $table = 'operator_departemen';
    public $timestamps = false;

    const TABLE = 'operator_departemen';
    const TITLE = 'Operator Departemen';
    
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

    const FIELD_DEFAULT_VALUE = [
        'user_id' => '',
        'name' => '',
        'phone_number' => '',
    ];

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "user_id" => [
            "operator" => "=",
        ],
        "name" => [
            "operator" => "=",
        ],
        "phone_number" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
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
