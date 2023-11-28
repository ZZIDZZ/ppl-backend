<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pkl extends Model
{

    // Schema::create('pkl', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->float('nilai', 16, 2)->default(0);
    //     $table->date('tanggal_selesai')->nullable();
    //     $table->boolean('is_lulus')->nullable();
    //     $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
    //     $table->foreignId('irs_id')->nullable()->constrained('irs');
    //     $table->text('file_pkl')->nullable();
    //     $table->string('status_code')->nullable()->comment('waiting_approval / approved / waiting_approval_over / approved_over');

    // });
    use HasFactory;

    const TABLE = 'pkl';

    const TITLE = 'Praktik Kerja Lapangan';
    protected $table = 'pkl';
    public $timestamps = true;


    const FIELDS = [
        'id',
        'nilai',
        'mahasiswa_id',
        'file_pkl',
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
        'nilai',
        'mahasiswa_id',
        'file_pkl',
        'status_code',
        'semester'
    ];
    const FIELD_SORTABLE = [
        'id',
        'nilai',
        'mahasiswa_id',
        'file_pkl',
        'status_code',
        'semester'
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_pkl',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'nilai' => 'nilai',
        'mahasiswa_id' => 'id mahasiswa',
        'file_pkl' => 'File PKL',
        'status_code' => 'kode status',
        'semester' => 'Semester'
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
        'nilai' => 'nullable',
        'mahasiswa_id' => 'required',
        'file_pkl' => 'nullable',
        'status_code' => 'nullable',
        'semester' => 'required',
    ];

    const FIELD_DEFAULT_VALUE = [
        'nilai' => 'A',
        'file_pkl' => null,
        'status_code' => 'waiting_approval',
        'semester' => null
    ];
    
    const FIELD_FILTERABLE = [
        "id" => [
            "operator" => "=",
        ],
        "nilai" => [
            "operator" => "=",
        ],
        "mahasiswa_id" => [
            "operator" => "=",
        ],
        "file_pkl" => [
            "operator" => "=",
        ],
        "status_code" => [
            "operator" => "=",
        ],
        "semester" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'nilai',
        'mahasiswa_id',
        'file_pkl',
        'status_code',
        'semester'
    ];

    public static function beforeInsert($input)
    {
        // check from Khs, left join with IRS to get sks_semester, if total sks_semester for all KHS is less than 100, then cannot create new Pkl
        // check if irs already exist in either khs, pkl, or skripsi, if yes then return error

        $mahasiswa_id = $input['mahasiswa_id'];
        $query = "SELECT COALESCE(SUM(i.sks_semester), 0) as total_sks FROM khs k LEFT JOIN irs i ON k.mahasiswa_id=i.mahasiswa_id AND k.semester=i.semester 
        WHERE k.mahasiswa_id=:mahasiswa_id";
        $params = [
            'mahasiswa_id' => $mahasiswa_id
        ];
        $total_sks = DB::select($query, $params)[0]->total_sks;
        if ($total_sks < 80) {
            dd($total_sks);
            throw new \Exception("Total SKS kurang dari 80, tidak bisa membuat PKL");
        }


        $input['status_code'] = 'waiting_approval';

        // check if semester already exist in pkl
        $pkl = Pkl::where('mahasiswa_id', $input['mahasiswa_id'])->where('semester', $input['semester'])->first();
        if ($pkl) {
            throw new \Exception("Semester sudah dipakai");
        }

        // change nilai to upper, and check if A, B, or C
        $nilai = $input['nilai'];
        $nilai = strtoupper($nilai);
        if ($nilai != 'A' && $nilai != 'B' && $nilai != 'C') {
            throw new \Exception("Nilai tidak valid");
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
