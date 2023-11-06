<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenWali extends Model
{
    use HasFactory;
    protected $table = 'dosen_wali';
    public $timestamps = true;

    // schema:
    // Schema::create('dosen_wali', function (Blueprint $table) {
    //     $table->bigIncrements('id')->unsigned();
    //     $table->foreignId('user_id')->nullable()->constrained('users');
    //     $table->string('name')->nullable();
    //     $table->string('phone_number')->nullable();
    //     $table->string('nip')->nullable();
    // });

    const TABLE = 'dosen_wali';
    const TITLE = 'Dosen Wali';
    
    const FIELDS = [
        'id',
        'user_id',
        'name',
        'phone_number',
        'nip',
    ];

    const FIELD_TYPES = [
        'id' => 'primary_key',
        'user_id' => 'foreign_key',
        'name' => 'string',
        'phone_number' => 'string',
        'nip' => 'string',
    ];

    const FIELD_INPUT = [
        'user_id',
        'name',
        'phone_number',
        'nip',
    ];

    const FIELD_SORTABLE = [
        'id',
        'user_id',
        'name',
        'phone_number',
        'nip',
    ];

    const FIELD_SEARCHABLE = [
        'name',
        'phone_number',
        'nip',
    ];

    const FIELD_ALIAS = [
        'id' => 'id',
        'user_id' => 'id user',
        'name' => 'Nama',
        'phone_number' => 'Nomor Telepon',
        'nip' => 'NIP',
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
        'nip' => 'nullable',
    ];

    const FIELD_DEFAULT_VALUE = [
        'user_id' => '',
        'name' => '',
        'phone_number' => '',
        'nip' => '',
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
        "nip" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'nip',
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
