<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Irs extends Model
{

    // Schema::create('irs', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->float('sks_semester', 16, 2)->default(0);
    //     $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
    //     $table->foreignId('riwayat_status_akademik_id')->nullable()->constrained('riwayat_status_akademik');
    //     $table->text('file_scan_irs')->nullable();
    // });
    use HasFactory;

    protected $table = 'irs';
    public $timestamps = true;
    const TABLE = 'irs';
    const TITLE = 'Isian Rencana Studi';

    const FIELDS = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'file_scan_irs',
        'status_code'
    ];
    const FIELD_TYPES = [
        // 'id' => 'primary_key',
        // 'user_id' => 'foreign_key',
        // 'name' => 'string',
        // 'phone_number' => 'string',
        // 'nim' => 'string',
    ];
    const FIELD_INPUT = [
        'sks_semester',
        'mahasiswa_id',
        'file_scan_irs',
    ];
    const FIELD_SORTABLE = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'file_scan_irs',
        'status_code'
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_scan_irs',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'sks_semester' => 'sks semester',
        'mahasiswa_id' => 'id mahasiswa',
        'file_scan_irs' => 'file scan irs',
        'status_code' => 'status code'
    ];

    // mahasiswa:
    // 'id',
    // 'user_id',
    // 'dosen_wali_id',
    // 'name',
    // 'phone_number',
    // 'nim',
    // 'password_changed',
    // 'tahun_masuk',
    // 'jalur_masuk',
    // 'status',
    const FIELD_RELATIONS = [
        'mahasiswa_id' => [
            'linkTable' => 'mahasiswa',
            'aliasTable' => 'A',
            'linkField' => 'id',
            'displayName' => 'name',
            'selectFields' => ['name', 'nim', 'tahun_masuk', 'jalur_masuk', 'status', 'dosen_wali_id'],
            'selectValue' => ['name', 'nim', 'tahun_masuk', 'jalur_masuk', 'status', 'dosen_wali_id'],
        ],
    ];

    const FIELD_VALIDATION = [
        'sks_semester' => 'nullable',
        'mahasiswa_id' => 'required',
    ];

    const FIELD_DEFAULT_VALUE = [
        'sks_semester' => 0,
        'mahasiswa_id' => '',
        'file_scan_irs' => '',
        'status_code' => 'waiting_approval'
    ];

    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "sks_semester" => [
            "operator" => "=",
        ],
        "mahasiswa_id" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'sks_semester',
        'mahasiswa_id',
        'file_scan_irs',
    ];

    public static function beforeInsert($input)
    {
        $input['status_code'] = 'waiting_approval';

        // check if input semeester is between 1-14
        $semester = $input['semester'];
        if ($semester < 1 || $semester > 14) {
            throw new \Exception("Semester tidak valid");
        }

        // check if semester already exist in irs
        $irs = Irs::where('mahasiswa_id', $input['mahasiswa_id'])->where('semester', $semester)->first();
        if ($irs) {
            throw new \Exception("Semester sudah dipakai");
        }

        // check if input semester is in order
        $irs = Irs::where('mahasiswa_id', $input['mahasiswa_id'])->get();
        $irs = $irs->toArray();
        $irs = array_map(function ($item) {
            return $item['semester'];
        }, $irs);
        $irs = array_unique($irs);
        sort($irs);
        if ($irs != range(1, count($irs))) {
            throw new \Exception("Semester tidak urut");
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
