<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Skripsi extends Model
{

    // Schema::create('skripsi', function (Blueprint $table) {
    //     $table->bigIncrements('id');
    //     $table->float('nilai', 16, 2)->default(0);
    //     $table->date('tanggal_selesai')->nullable();
    //     $table->boolean('is_lulus')->nullable();
    //     $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
    //     $table->foreignId('irs_id')->nullable()->constrained('irs');
    //     $table->text('file_skripsi')->nullable();
    //     $table->string('status_code')->nullable()->comment('waiting_approval / approved / waiting_approval_over / approved_over');
    // });
    use HasFactory;

    const TABLE = 'skripsi';

    const TITLE = 'Skripsi';
    protected $table = 'skripsi';
    public $timestamps = true;


    const FIELDS = [
        'id',
        'nilai',
        'tanggal_selesai',
        'is_lulus',
        'mahasiswa_id',
        'irs_id',
        'file_skripsi',
        'status_code',
        'semester_akademik_id',
        'is_selesai'
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
        'tanggal_selesai',
        'is_lulus',
        'mahasiswa_id',
        'irs_id',
        'file_skripsi',
        'status_code',
        'is_selesai',
        'semester_akademik_id'
    ];
    const FIELD_SORTABLE = [
        'id',
        'nilai',
        'tanggal_selesai',
        'is_lulus',
        'mahasiswa_id',
        'irs_id',
        'file_skripsi',
        'status_code',
        'semester_akademik_id',
        'is_selesai'
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_skripsi',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'nilai' => 'nilai',
        'mahasiswa_id' => 'id mahasiswa',
        'irs_id' => 'id riwayat status akademik',
        'file_skripsi' => 'File Skripsi',
        'status_code' => 'kode status',
        'semester_akademik_id' => 'id semester akademik'
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
        'irs_id' => [
            'linkTable' => 'irs',
            'aliasTable' => 'B',
            'linkField' => 'id',
            'displayName' => 'irs',
            'selectFields' => ['semester_akademik_id'],
            'selectValue' => ['semester_akademik_id'],
        ],
        'semester_akademik_id' => [
            'linkTable' => 'semester_akademik',
            'aliasTable' => 'C',
            'linkField' => 'id',
            'displayName' => 'semester_akademik',
            'selectFields' => ['tahun_ajaran', 'semester'],
            'selectValue' => ['tahun_ajaran', 'semester'],
        ],
    ];

    const FIELD_VALIDATION = [
        'nilai' => 'nullable',
        'mahasiswa_id' => 'required',
        'irs_id' => 'required',
        'file_skripsi' => 'nullable',
        'status_code' => 'nullable',
        'semester_akademik_id' => 'nullable',
        'tanggal_selesai' => 'nullable',
        'is_lulus' => 'nullable',
        'is_selesai' => 'required'
        
    ];

    const FIELD_DEFAULT_VALUE = [
        'nilai' => null,
        'file_skripsi' => null,
        'status_code' => 'waiting_approval',
        'tanggal_selesai' => null,
        'is_lulus' => null,
        'is_selesai' => false
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
        "irs_id" => [
            "operator" => "=",
        ],
        "file_skripsi" => [
            "operator" => "=",
        ],
        "status_code" => [
            "operator" => "=",
        ],
        "semester_akademik_id" => [
            "operator" => "=",
        ],
        "tanggal_selesai" => [
            "operator" => "=",
        ],
        "is_lulus" => [
            "operator" => "=",
        ],
        "is_selesai" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'nilai',
        'mahasiswa_id',
        'irs_id',
        'file_skripsi',
        'status_code',
        'semester_akademik_id',
        'tanggal_selesai',
        'is_lulus',
        'is_selesai'
    ];

    public static function beforeInsert($input)
    {
        // check if irs already exist in either khs, pkl, or skripsi, if yes then return error
        if (Pkl::where('irs_id', $input['irs_id'])->first()) {
            // throw error
            throw new \Exception("IRS sudah dipakai");
        }
        if (Skripsi::where('irs_id', $input['irs_id'])->first()) {
            // throw error
            throw new \Exception("IRS sudah dipakai");
        }
        $mahasiswa_id = $input['mahasiswa_id'];
        $query = "SELECT COALESCE(SUM(i.sks_semester), 0) as total_sks FROM khs k LEFT JOIN irs i ON k.irs_id=i.id WHERE k.mahasiswa_id=:mahasiswa_id";
        $params = [
            'mahasiswa_id' => $mahasiswa_id
        ];
        $total_sks = DB::select($query, $params)[0]->total_sks;
        if ($total_sks < 100) {
            throw new \Exception("Total SKS kurang dari 100, tidak bisa membuat Skripsi");
        }

        $irs_id = $input['irs_id'];
        $semester_akademik_id = Irs::where('id', $irs_id)->first()->semester_akademik_id;
        $input['semester_akademik_id'] = $semester_akademik_id;

        $input['status_code'] = 'waiting_approval';
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
        // check if skripsi is lulus and is selesai, if yes then update mahasiswa status to lulus
        // if ($input['is_lulus'] == true && $input['is_selesai'] == true) {
        //     $mahasiswa = Mahasiswa::where('id', $input['mahasiswa_id'])->first();
        //     $mahasiswa->status = 'Lulus';
        //     $mahasiswa->save();
        // }
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
