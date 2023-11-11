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
        'semester_akademik_id',
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
        'semester_akademik_id'
    ];
    const FIELD_SORTABLE = [
        'id',
        'sks_semester',
        'mahasiswa_id',
        'file_scan_irs',
        'semester_akademik_id',
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
        'semester_akademik_id' => 'id semester akademik',
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
        'sks_semester' => 'nullable',
        'mahasiswa_id' => 'required',
        'semester_akademik_id' => 'nullable'
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
        "semester_akademik_id" => [
            "operator" => "=",
        ],
    ];

    protected $fillable = [
        'sks_semester',
        'mahasiswa_id',
        'file_scan_irs',
        'semester_akademik_id'
    ];

    public static function beforeInsert($input)
    {
        $input['status_code'] = 'waiting_approval';

        // check if semester_akademik is not less than mahasiswa's tahun_masuk, and not more than mahasiswa's tahun_masuk + 6
        $mahasiswa = Mahasiswa::find($input['mahasiswa_id']);
        $semester_akademik = SemesterAkademik::find($input['semester_akademik_id']);
        $tahun_ajaran = $semester_akademik->tahun_ajaran;
        $semester = $semester_akademik->semester;
        $tahun_masuk = $mahasiswa->tahun_masuk;
        $tahun_lulus = $tahun_masuk + 6;
        if ($tahun_ajaran < $tahun_masuk || $tahun_ajaran > $tahun_lulus) {
            throw new \Exception("Tahun ajaran tidak valid");
        }

        // check if mahasiswa has already made irs for this semester
        $irs = Irs::where('mahasiswa_id', $input['mahasiswa_id'])->where('semester_akademik_id', $input['semester_akademik_id'])->first();
        if ($irs) {
            throw new \Exception("IRS untuk semester ini sudah dibuat");
        }

        // check if the semester and tahun akademik is in order from previous IRS
        // $semester_akademik = SemesterAkademik::find($input['semester_akademik_id']);
        // $semester = $semester_akademik->semester;
        // if($semester == 1){
        //     $tahun_ajaran = $semester_akademik->tahun_ajaran;
        //     $tahun_ajaran_sebelumnya = $tahun_ajaran - 1;
        //     $semester_akademik_sebelumnya = SemesterAkademik::where('tahun_ajaran', $tahun_ajaran_sebelumnya)->where('semester', 2)->first();
        // } else {
        //     $tahun_ajaran = $semester_akademik->tahun_ajaran;
        //     $semester_akademik_sebelumnya = SemesterAkademik::where('tahun_ajaran', $tahun_ajaran)->where('semester', 1)->first();
        // }
        // $irs_sebelumnya = Irs::where('mahasiswa_id', $input['mahasiswa_id'])->where('semester_akademik_id', $semester_akademik_sebelumnya->id)->first();
        // if(!$irs_sebelumnya){
        //     throw new \Exception("Pembuatan IRS harus urut");
        // }
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
