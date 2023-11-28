<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Khs extends Model
{

    // Schema::create('khs', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->float('ip_semester', 16, 2)->default(0);
    //     $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
    //     $table->foreignId('irs_id')->nullable()->constrained('irs');
    //     $table->text('file_scan_khs')->nullable();
    //     $table->string('status_code')->nullable()->comment('waiting_approval / approved');
    // });
    use HasFactory;

    const TABLE = 'khs';

    const TITLE = 'Kartu Hasil Studi';
    protected $table = 'khs';
    public $timestamps = true;


    const FIELDS = [
        'id',
        'ip_semester',
        'mahasiswa_id',
        'file_scan_khs',
        'status_code',
        'semester'
    ];
    const FIELD_TYPES = [
        // 'id',
        // 'ip_semester',
        // 'mahasiswa_id',
        // 'irs_id',
        // 'file_scan_khs',
        // 'status_code',
    ];
    const FIELD_INPUT = [
        'ip_semester',
        'mahasiswa_id',
        'file_scan_khs',
        'semester',
        'status_code'
    ];
    const FIELD_SORTABLE = [
        'id',
        'ip_semester',
        'mahasiswa_id',
        'file_scan_khs',
        'status_code',
        'semester'
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_scan_khs',
        'status_code',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'ip_semester' => 'ip semester',
        'mahasiswa_id' => 'id mahasiswa',
        'file_scan_khs' => 'file scan khs',
        'status_code' => 'kode status',
    ];
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
        'ip_semester' => 'nullable',
        'mahasiswa_id' => 'required',
        'file_scan_irs' => 'nullable',
        'status_code' => 'nullable',
    ];

    const FIELD_DEFAULT_VALUE = [
        'ip_semester' => 0,
        'mahasiswa_id' => '',
        'file_scan_irs' => '',
        'status_code' => 'waiting_approval'
    ];
    
    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "ip_semester" => [
            "operator" => "=",
        ],
        "mahasiswa_id" => [
            "operator" => "=",
        ],
        "file_scan_khs" => [
            "operator" => "=",
        ],
        "status_code" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'ip_semester',
        'mahasiswa_id',
        'irs_id',
        'file_scan_khs',
        'status_code',
        'semester_akademik_id'
    ];

    public static function beforeInsert($input)
    {
        // check if irs already exist in either khs, pkl, or skripsi, if yes then return error
        // $semester_akademik_id = Irs::where('id', $input["irs_id"])->first()->semester_akademik_id;
        // dd($input["irs_id"], $semester_akademik_id);
        // check khs between 0.00 - 4.00
        if ($input['ip_semester'] < 0.00 || $input['ip_semester'] > 4.00) {
            throw new \Exception("IP Semester harus diantara 0.00 - 4.00");
        }
        
        $input['status_code'] = 'waiting_approval';

        // check if input semeester is between 1-14
        $semester = $input['semester'];
        if ($semester < 1 || $semester > 14) {
            throw new \Exception("Semester tidak valid");
        }

        // check if semester already exist in irs
        $khs = Khs::where('mahasiswa_id', $input['mahasiswa_id'])->where('semester', $semester)->fkhst();
        if ($khs) {
            throw new \Exception("Semester sudah dipakai");
        }

        // check if input semester is in order
        $khs = Khs::where('mahasiswa_id', $input['mahasiswa_id'])->get();
        $khs = $khs->toArray();
        $khs = array_map(function ($item) {
            return $item['semester'];
        }, $khs);
        $khs = array_unique($khs);
        sort($khs);
        if ($khs != range(1, count($khs))) {
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
