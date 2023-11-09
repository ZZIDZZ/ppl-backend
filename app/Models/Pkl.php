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
        'tanggal_selesai',
        'is_lulus',
        'mahasiswa_id',
        'irs_id',
        'file_pkl',
        'status_code',
        'semester_akademik_id'
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
        'file_pkl',
        'status_code',
    ];
    const FIELD_SORTABLE = [
        'id',
        'nilai',
        'tanggal_selesai',
        'is_lulus',
        'mahasiswa_id',
        'irs_id',
        'file_pkl',
        'status_code',
        'semester_akademik_id'
    ];
    //searchable untuk tipe string and text!
    const FIELD_SEARCHABLE = [
        'file_pkl',
    ];
    const FIELD_ALIAS = [
        'id' => 'id',
        'nilai' => 'nilai',
        'mahasiswa_id' => 'id mahasiswa',
        'irs_id' => 'id irs',
        'file_pkl' => 'File PKL',
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
            'selectFields' => ['sks_semester'],
            'selectValue' => ['sks_semester'],
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
        'file_pkl' => 'nullable',
        'status_code' => 'nullable',
        'semester_akademik_id' => 'nullable',
        'tanggal_selesai' => 'nullable',
        'is_lulus' => 'nullable',
        
    ];

    const FIELD_DEFAULT_VALUE = [
        'nilai' => null,
        'file_pkl' => null,
        'status_code' => 'waiting_approval',
        'tanggal_selesai' => null,
        'is_lulus' => null,
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
        "file_pkl" => [
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
    ];

    protected $fillable = [
        'nilai',
        'mahasiswa_id',
        'irs_id',
        'file_pkl',
        'status_code',
        'semester_akademik_id',
        'tanggal_selesai',
        'is_lulus',
    ];

    public static function beforeInsert($input)
    {
        // check from Khs, left join with IRS to get sks_semester, if total sks_semester for all KHS is less than 100, then cannot create new Pkl

        $mahasiswa_id = $input['mahasiswa_id'];
        $query = "SELECT COALESCE(SUM(i.sks_semester), 0) as total_sks FROM khs k LEFT JOIN irs i ON k.irs_id=i.id WHERE k.mahasiswa_id=:mahasiswa_id";
        $params = [
            'mahasiswa_id' => $mahasiswa_id
        ];
        $total_sks = DB::select($query, $params)[0]->total_sks;
        if ($total_sks < 100) {
            throw new \Exception("Total SKS kurang dari 100, tidak bisa membuat PKL");
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
