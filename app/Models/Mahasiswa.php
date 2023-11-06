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

    //Table => Nama tabel
    const TABLE = 'mahasiswa';
    //Title menu
    const TITLE = 'Mahasiswa';
    
    const FIELDS = [
        'id',
        'user_id',
        'dosen_wali_id',
        'name',
        'phone_number',
        'nim',
        'password_changed'
    ];

    const FIELD_TYPES = [
        // 'id' => 'primary_key',
        // 'user_id' => 'foreign_key',
        // 'name' => 'string',
        // 'phone_number' => 'string',
        // 'nim' => 'string',
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

    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'name',
        'phone_number',
        'nim',
    ];

    //
    const FIELD_ALIAS = [
        'id' => 'id',
        'user_id' => 'id user',
        'dosen_wali_id' => 'id dosen wali',
        'name' => 'Nama',
        'phone_number' => 'Nomor Telepon',
        'nim' => 'NIM',
    ];

    //linktable = > source tablenya
    //linkfield adalah apa yang harus dihubungkan / apa idnya
    //display name => notUsed
    //SelectFields => 
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
            'selectValue' => ['dosen_wali_name'],
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

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "user_id" => [
            "operator" => "=",
        ],
        "dosen_wali_id" => [
            "operator" => "=",
        ],
        "name" => [
            "operator" => "=",
        ],
        "phone_number" => [
            "operator" => "=",
        ],
        "nim" => [
            "operator" => "=",
        ],
        "password_changed" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'nim',
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
