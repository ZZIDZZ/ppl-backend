<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    use HasFactory;
    protected $table = 'cities';
    public $timestamps = true;

    // Schema::create('cities', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->string('name')->unique();
    //     $table->string('code')->nullable(true);
    //     $table->longText('description')->nullable(true);
    //     $table->foreignId('province_id');
    //     $table->timestampsTz($precision = 0);
    // });

    //Table => Nama tabel
    const TABLE = 'cities';
    //Title menu
    const TITLE = 'Kota';
    
    const FIELDS = [
        'id',
        'name',
        'code',
        'description',
        'province_id',
        'created_at',
        'updated_at',
    ];

    const FIELD_TYPES = [
        // 'id' => 'primary_key',
        // 'user_id' => 'foreign_key',
        // 'name' => 'string',
        // 'phone_number' => 'string',
        // 'nim' => 'string',
    ];

    const FIELD_INPUT = [
        'name',
        'code',
        'description',
        'province_id',
    ];

    const FIELD_SORTABLE = [
        'id',
        'name',
        'code',
        'description',
        'province_id',
        'created_at',
        'updated_at',
    ];

    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'name',
        'code',
        'description',
    ];

    //
    const FIELD_ALIAS = [
        'id' => 'id',
        'name' => 'name',
        'code' => 'code',
        'description' => 'description',
        'province_id' => 'province_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];

    //linktable = > source tablenya
    //linkfield adalah apa yang harus dihubungkan / apa idnya
    //display name => notUsed
    //SelectFields => 
    const FIELD_RELATIONS = [
        'province_id' => [
            'linkTable' => 'provinces',
            'aliasTable' => 'A',
            'linkField' => 'id',
            'displayName' => 'email',
            'selectFields' => ['name'],
            'selectValue' => ['province_name'],
        ],
    ];

    const FIELD_VALIDATION = [
        'name' => 'required',
        'code' => 'nullable',
        'description' => 'nullable',
        'province_id' => 'required',
    ];

    const FIELD_DEFAULT_VALUE = [
        'name' => '',
        'code' => '',
        'description' => '',
        'province_id' => '',
    ];

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "name" => [
            "operator" => "like",
        ],
        "code" => [
            "operator" => "like",
        ],
        "description" => [
            "operator" => "like",
        ],
        "province_id" => [
            "operator" => "=",
        ],
        "created_at" => [
            "operator" => "=",
        ],
        "updated_at" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'name',
        'code',
        'description',
        'province_id',
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
