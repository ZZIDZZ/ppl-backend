<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;
    protected $table = 'mahasiswa';
    public $timestamps = true;

    // Schema::create('mahasiswa', function (Blueprint $table) {
    //     $table->bigIncrements('id')->unsigned();
    //     $table->foreignId('user_id')->nullable()->constrained('users');
    //     $table->foreignId('dosen_wali_id')->constrained('dosen_wali');
    //     $table->string('name')->nullable();
    //     $table->string('phone_number')->nullable();
    //     $table->string('nim')->unique();
    //     $table->string('email')->nullable();
    //     $table->bigInteger('tahun_masuk')->nullable();
    //     $table->boolean('password_changed')->default(false)->comment('0: Butuh Ganti Password, 1: Password Sudah Diganti');
    //     $table->string('jalur_masuk')->nullable();
    //     $table->string('status')->nullable()->default('Aktif')->comment('Aktif, Cuti, Mangkir, DO, Undur Diri, Lulus, dan Meninggal Dunia');
    //     $table->timestampsTz($precision = 0);
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
        'password_changed',
        'tahun_masuk',
        'jalur_masuk',
        'status',
        'created_at',
        'updated_at',
        'city_id',
        'file_profile'
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
        'tahun_masuk',
        'jalur_masuk',
        'status',
        'city_id',
        'file_profile'
    ];

    const FIELD_SORTABLE = [
        'id',
        'user_id',
        'dosen_wali_id',
        'name',
        'phone_number',
        'nim',
        'password_changed',
        'tahun_masuk',
        'jalur_masuk',
        'status',
        'created_at',
        'updated_at',
        'city_id',
        'file_profile'
    ];

    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'name',
        'phone_number',
        'nim',
        'jalur_masuk',
        'status',
    ];

    //
    const FIELD_ALIAS = [
        'id' => 'id',
        'user_id' => 'Id User',
        'dosen_wali_id' => 'Id Dosen Wali',
        'name' => 'Nama',
        'phone_number' => 'Nomor Telepon',
        'nim' => 'NIM',
        'password_changed' => 'Password Changed',
        'tahun_masuk' => 'Tahun Masuk',
        'jalur_masuk' => 'Jalur Masuk',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'city_id' => 'City Id',
        'file_profile' => 'Foto Profil'
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
            'displayName' => 'username',
            'selectFields' => ['username'],
            'selectValue' => ['username'],
        ],
        'dosen_wali_id' => [
            'linkTable' => 'dosen_wali',
            'aliasTable' => 'B',
            'linkField' => 'id',
            'displayName' => 'dosen_wali',
            'selectFields' => ['name'],
            'selectValue' => ['dosen_wali_name'],
        ],
        'city_id' => [
            'linkTable' => 'cities',
            'aliasTable' => 'C',
            'linkField' => 'id',
            'displayName' => 'city',
            'selectFields' => ['name'],
            'selectValue' => ['city_name'],
        ],
    ];

    const FIELD_VALIDATION = [
        'user_id' => 'required',
        'dosen_wali_id' => 'required',
        'name' => 'required',
        'phone_number' => 'nullable',
        'nim' => 'required',
        'tahun_masuk' => 'nullable',
        'jalur_masuk' => 'nullable',
        'status' => 'required',
        'city_id' => 'nullable',
        'file_profile' => 'nullable',
    ];

    const FIELD_DEFAULT_VALUE = [
        'user_id' => '',
        'dosen_wali_id' => '',
        'name' => '',
        'phone_number' => '',
        'nim' => '',
        'password_changed' => '',
        'tahun_masuk' => '',
        'jalur_masuk' => '',
        'status' => 'Aktif',
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
        "phone_number" => [
            "operator" => "like",
        ],
        "nim" => [
            "operator" => "like",
        ],
        "password_changed" => [
            "operator" => "=",
        ],
        "tahun_masuk" => [
            "operator" => "=",
        ],
        "jalur_masuk" => [
            "operator" => "=",
        ],
        "status" => [
            "operator" => "=",
        ],
        "created_at" => [
            "operator" => "=",
        ],
        "updated_at" => [
            "operator" => "=",
        ],
        "city_id" => [
            "operator" => "=",
        ],
        "file_profile" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'user_id',
        'dosen_wali_id',
        'name',
        'phone_number',
        'nim',
        'password_changed',
        'tahun_masuk',
        'jalur_masuk',
        'status',
        'city_id',
        'file_profile'
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
