<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class RekapController extends Controller
{

    protected function is_blank($array, $key)
    {
        return isset($array[$key]) ? (is_null($array[$key]) || $array[$key] === "") : true;
    }

    public function rekapPklAngkatan(Request $request){
        $input = $request->all();
        $validation = [
            "start_tahun_angkatan" => "nullable",
            "end_tahun_angkatan" => "nullable",
            "dosen_wali_id" => "nullable"
        ];

        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return [
                "success" => false,
                "message" => $validator->errors()->first()
            ];
        }
        // if not exists input for start or end tahun angkatan, then use default value
        $start_tahun_angkatan = $input["start_tahun_angkatan"] ?? 2017;
        $end_tahun_angkatan = $input["end_tahun_angkatan"] ?? 2023;
        // check if current user is dosen wali
        $dosen_wali_id = null;
        $user_id = auth('api')->user()->id;
        $dosen_wali = DB::selectOne("
            SELECT 
                dw.id,
                dw.user_id,
                dw.name,
                dw.phone_number,
                dw.nip
            FROM dosen_wali dw
            WHERE dw.user_id = :user_id
        ", [
            "user_id" => $user_id
        ]);
        if(!is_null($dosen_wali)){
            $dosen_wali_id = $dosen_wali->id;
        }
        // dd($dosen_wali_id, $dosen_wali_id != null);
        $dosen_wali_where = $dosen_wali_id != null ? " WHERE m.dosen_wali_id = $dosen_wali_id " : " ";

        $params = [
            "start_tahun_angkatan" => (int)$start_tahun_angkatan,
            "end_tahun_angkatan" => (int)$end_tahun_angkatan
        ];

        $query = DB::select("
        WITH RECURSIVE YearSequence AS (
            SELECT :start_tahun_angkatan::integer AS Year
            UNION ALL
            SELECT Year + 1
            FROM YearSequence
            WHERE Year < :end_tahun_angkatan::integer
          )
          
          SELECT 
            ys.Year AS tahun_masuk,
            COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
            COALESCE(r.belum_lulus, 0) AS belum_lulus
          FROM 
            YearSequence ys
          LEFT JOIN (
            SELECT 
              m.tahun_masuk,
              SUM(CASE WHEN COALESCE(p.nilai, 'X') IN ('A', 'B', 'C') THEN 1 ELSE 0 END) AS sudah_lulus,
              SUM(CASE WHEN COALESCE(p.nilai, 'X') = 'X' AND p.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
            FROM 
              mahasiswa m
            LEFT JOIN 
              pkl p ON p.mahasiswa_id = m.id
            ". $dosen_wali_where ."
            GROUP BY 
              m.tahun_masuk
          ) r ON ys.Year = r.tahun_masuk
          ORDER BY 
            ys.Year DESC
        ", $params);

        $tahun_masuk_data = [];
        $status_lulus_data = [];

        foreach($query as $key => $value){
            $tahun_masuk_data[] = $value->tahun_masuk;
            $lulus_data = [];
            $lulus_data["sudah_lulus"] = $value->sudah_lulus;
            $lulus_data["belum_lulus"] = $value->belum_lulus;
            $status_lulus_data[] = $lulus_data;
        }
        $data = [
            "tahun_masuk" => $tahun_masuk_data,
            "status_lulus" => $status_lulus_data
        ];
        return [
            "success" => true,
            "data" => $data
        ];
    }

    public function listPklAngkatan(Request $request){
        // inputs: tahun_angkatan, is_lulus
        $input = $request->all();
        
        // validate input
        $tahun_angkatan = $input["tahun_angkatan"] ?? null;
        $is_lulus = $input["is_lulus"] ?? null;
        if(is_string($input["is_lulus"])){
            $is_lulus = $input["is_lulus"] == "true" ? true : false;
        }

        $validation = [
            "tahun_angkatan" => "required",
            "is_lulus" => "required",
            "dosen_wali_id" => "nullable"
        ];

        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return [
                "success" => false,
                "message" => $validator->errors()->first()
            ];
        }
        $dosen_wali_id = null;
        $user_id = auth('api')->user()->id;
        $dosen_wali = DB::selectOne("
            SELECT 
                dw.id,
                dw.user_id,
                dw.name,
                dw.phone_number,
                dw.nip
            FROM dosen_wali dw
            WHERE dw.user_id = :user_id
        ", [
            "user_id" => $user_id
        ]);
        if(!is_null($dosen_wali)){
            $dosen_wali_id = $dosen_wali->id;
        }

        $dosen_wali_where = $dosen_wali_id != null ? " m.dosen_wali_id = $dosen_wali_id AND " : " ";
        $is_lulus_where = "";
        if($is_lulus == true){
            $is_lulus_where = " AND nilai IS NOT NULL "; 
        }
        else{
            $is_lulus_where = " AND nilai IS NULL ";
        }
        


        $params = [];

        $query = DB::select("
        SELECT 
            m.id,
            m.name,
            m.nim,
            m.tahun_masuk,
            m.jalur_masuk,
            m.status,
            m.created_at,
            m.updated_at,
            m.city_id,
            m.file_profile,
            p.id as pkl_id,
            p.nilai,
            p.mahasiswa_id,
            p.file_pkl,
            p.status_code
        FROM
            mahasiswa m
        LEFT JOIN
            pkl p ON p.mahasiswa_id = m.id
        WHERE
        ". $dosen_wali_where ."
            m.tahun_masuk = :tahun_angkatan " . $is_lulus_where . "
        ", [
            "tahun_angkatan" => $tahun_angkatan,
        ]);
        
        return [
            "success" => true,
            "data" => $query
        ];
    }

    public function rekapSkripsiAngkatan(Request $request){
        $input = $request->all();
        $validation = [
            "start_tahun_angkatan" => "nullable",
            "end_tahun_angkatan" => "nullable",
            "dosen_wali_id" => "nullable"
        ];

        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return [
                "success" => false,
                "message" => $validator->errors()->first()
            ];
        }
        // if not exists input for start or end tahun angkatan, then use default value
        $start_tahun_angkatan = $input["start_tahun_angkatan"] ?? 2017;
        $end_tahun_angkatan = $input["end_tahun_angkatan"] ?? 2023;
        // check if current user is dosen wali
        $dosen_wali_id = null;
        $user_id = auth('api')->user()->id;
        $dosen_wali = DB::selectOne("
            SELECT 
                dw.id,
                dw.user_id,
                dw.name,
                dw.phone_number,
                dw.nip
            FROM dosen_wali dw
            WHERE dw.user_id = :user_id
        ", [
            "user_id" => $user_id
        ]);
        if(!is_null($dosen_wali)){
            $dosen_wali_id = $dosen_wali->id;
        }
        // dd($dosen_wali_id, $dosen_wali_id != null);
        $dosen_wali_where = $dosen_wali_id != null ? " WHERE m.dosen_wali_id = $dosen_wali_id " : " ";

        $params = [
            "start_tahun_angkatan" => (int)$start_tahun_angkatan,
            "end_tahun_angkatan" => (int)$end_tahun_angkatan
        ];

        $query = DB::select("
        WITH RECURSIVE YearSequence AS (
            SELECT :start_tahun_angkatan::integer AS Year
            UNION ALL
            SELECT Year + 1
            FROM YearSequence
            WHERE Year < :end_tahun_angkatan::integer
          )
          
          SELECT 
            ys.Year AS tahun_masuk,
            COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
            COALESCE(r.belum_lulus, 0) AS belum_lulus
          FROM 
            YearSequence ys
          LEFT JOIN (
            SELECT 
              m.tahun_masuk,
              SUM(CASE WHEN COALESCE(s.nilai, 'X') IN ('A', 'B', 'C') THEN 1 ELSE 0 END) AS sudah_lulus,
              SUM(CASE WHEN COALESCE(s.nilai, 'X') = 'X' AND s.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
            FROM 
              mahasiswa m
            LEFT JOIN 
              skripsi s ON s.mahasiswa_id = m.id
            ". $dosen_wali_where ."
            GROUP BY 
              m.tahun_masuk
          ) r ON ys.Year = r.tahun_masuk
          ORDER BY 
            ys.Year DESC;
        ", $params);

        $tahun_masuk_data = [];
        $status_lulus_data = [];

        foreach($query as $key => $value){
            $tahun_masuk_data[] = $value->tahun_masuk;
            $lulus_data = [];
            $lulus_data["sudah_lulus"] = $value->sudah_lulus;
            $lulus_data["belum_lulus"] = $value->belum_lulus;
            $status_lulus_data[] = $lulus_data;
        }
        $data = [
            "tahun_masuk" => $tahun_masuk_data,
            "status_lulus" => $status_lulus_data
        ];
        return [
            "success" => true,
            "data" => $data
        ];
    }

    public function listSkripsiAngkatan(Request $request){
        // inputs: tahun_angkatan, is_lulus
        $input = $request->all();
        
        // validate input
        $tahun_angkatan = $input["tahun_angkatan"] ?? null;
        $is_lulus = $input["is_lulus"] ?? null;
        if(is_string($input["is_lulus"])){
            $is_lulus = $input["is_lulus"] == "true" ? true : false;
        }

        $validation = [
            "tahun_angkatan" => "required",
            "is_lulus" => "required",
            "dosen_wali_id" => "nullable"
        ];

        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return [
                "success" => false,
                "message" => $validator->errors()->first()
            ];
        }
        $dosen_wali_id = null;
        $user_id = auth('api')->user()->id;
        $dosen_wali = DB::selectOne("
            SELECT 
                dw.id,
                dw.user_id,
                dw.name,
                dw.phone_number,
                dw.nip
            FROM dosen_wali dw
            WHERE dw.user_id = :user_id
        ", [
            "user_id" => $user_id
        ]);
        if(!is_null($dosen_wali)){
            $dosen_wali_id = $dosen_wali->id;
        }

        $dosen_wali_where = $dosen_wali_id != null ? " m.dosen_wali_id = $dosen_wali_id AND " : " ";
        $is_lulus_where = " ";
        if($is_lulus == true){
            $is_lulus_where = " AND nilai IS NOT NULL "; 
        }
        else{
            $is_lulus_where = " AND nilai IS NULL ";
        }

        $params = [];

        $query = DB::select("
        SELECT 
            m.id,
            m.name,
            m.nim,
            m.tahun_masuk,
            m.jalur_masuk,
            m.status,
            m.created_at,
            m.updated_at,
            m.city_id,
            m.file_profile,
            s.id as skripsi_id,
            s.nilai,
            s.mahasiswa_id,
            s.file_skripsi,
            s.status_code
        FROM
            mahasiswa m
        LEFT JOIN
            skripsi s ON s.mahasiswa_id = m.id
        WHERE
        ". $dosen_wali_where ."
            m.tahun_masuk = :tahun_angkatan " . $is_lulus_where . "
        ", [
            "tahun_angkatan" => $tahun_angkatan,
        ]);
        
        return [
            "success" => true,
            "data" => $query
        ];
    }


    public function listSemesterMahasiswa(Request $request){
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = [];
        $sortableList = ["semester"];
        $filterableList = [
        ];
        $params = [];
        $user_id = auth('api')->user()->id;

        // get all request params
        $input = $request->all();
        $mahasiswa_id = $input["mahasiswa_id"] ?? null;
        if(is_null($mahasiswa_id)){
            return [
                "success" => false,
                "message" => "mahasiswa_id is required"
            ];
        }

        $params["mahasiswa_id"] = $mahasiswa_id;
        $sort = strtoupper($input["sort"] ?? "DESC") == "ASC" ? "ASC" : "DESC";
    
        $sortBy = "semester";


        if (in_array($input["sort_by"] ?? "", $sortableList)) {
            $sortBy = $input["sort_by"];
        }

        $tableJoinList = [];
        $filterList = [];

        if (!$this->is_blank($input, "search")) {
            // ILIKE not supported on mysql only postgres
            $searchableList = [];
            foreach ($searchedList as $key => $value) {
                $searchableList[] = " UPPER($value) ILIKE :search$key ";
            }
        } else {
            $searchableList = [];
        }
        
        if (count($searchableList) > 0 && !$this->is_blank($input, "search")) {
            for ($i = 0; $i < count($searchedList); $i++) {
                $params["search$i"] = "%" . strtoupper($input["search"] ?? "") . "%";
            }
        }

        foreach ($filterableList as $filter => $operator) {
            if (!$this->is_blank($input, $filter)) {
                $cekTypeInput = json_decode($input[$filter], true);
                if (!is_array($cekTypeInput)) {
                    $filterList[] = " AND " . $filter .  " " . $operator["operator"] . " :$filter";
                    $params[$filter] = $input[$filter];
                } else {
                    $input[$filter] = json_decode($input[$filter], true);
                    if ($input[$filter]["operator"] == 'between') {
                        $filterList[] = " AND " . $filter .  " " . $input[$filter]["operator"] . " '" . $input[$filter]["value"][0] . "' AND '" . $input[$filter]["value"][1] . "'";
                    } else if ($input[$filter]["operator"] == 'in') {
                        $inValues = "'" . implode("','", $input[$filter]["value"]) . "'";
                        $filterList[] = " AND " . $filter .  " in (" . $inValues . ")";
                    } else if ($input[$filter]["operator"] == 'ILIKE') {
                        $filterList[] = " AND " . $filter .  " " . $input[$filter]["operator"] . " '%" . $input[$filter]["value"] . "%'";
                    } else if ($input[$filter]["operator"] == 'IS NOT NULL') {
                        $filterList[] = " AND " . $filter .  " " . $input[$filter]["operator"];
                    } else if ($input[$filter]["operator"] == 'IS NULL') {
                        $filterList[] = " AND " . $filter .  " " . $input[$filter]["operator"];
                    } else {
                        $filterList[] = " AND " . $filter .  " " . $input[$filter]["operator"] . " :$filter";
                        $params[$filter] = $input[$filter]["value"];
                    }
                }
            }
        }

        
        $limit = $input["limit"] ?? 100;
        $offset = $input["offset"] ?? 0;
        if (!is_null($input["page"] ?? null)) {
            $offset = $limit * ($input["page"] - 1);
        }
        // change reminder_day_config to integer
        // Fetch data from the SQL query
        $sql = "SELECT * FROM (
            SELECT irs.semester as semester,
            CASE 
            WHEN irs.id IS NOT NULL THEN true 
            ELSE false END as is_irs, 
            CASE 
            WHEN k.id IS NOT NULL THEN true 
            ELSE false END as is_khs, 
            CASE 
            WHEN p.id IS NOT NULL THEN true 
            ELSE false END as is_pkl,
            CASE 
            WHEN s.id IS NOT NULL THEN true 
            ELSE false END as is_skripsi,
            irs.id, 
            irs.sks_semester as sks_semester, 
            k.ip_semester as ip_semester,
            p.nilai as nilai_pkl,
            s.nilai as nilai_skripsi,
            irs.file_scan_irs as file_scan_irs,
            k.file_scan_khs as file_scan_khs,
            p.file_pkl as file_pkl,
            s.file_skripsi as file_skripsi
            FROM irs
            LEFT JOIN khs k ON k.mahasiswa_id = irs.mahasiswa_id AND k.semester = irs.semester
            LEFT JOIN pkl p ON p.mahasiswa_id = irs.mahasiswa_id AND p.semester = irs.semester
            LEFT JOIN skripsi s ON s.mahasiswa_id = irs.mahasiswa_id AND s.semester = irs.semester
            WHERE irs.mahasiswa_id = :mahasiswa_id
            ORDER BY irs.semester
            ) as dummy
            ";
        // dd("
        // SELECT * FROM (
        //     ". $sql .") as dummy WHERE true ". (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")"  : "").
        //     implode("\n", $filterList) .  "  ORDER BY " . $sortBy . " " . $sort . " LIMIT $limit OFFSET $offset 
        //     ", $params);
        $data = DB::select("
        SELECT * FROM (
            ". $sql .") as dummy WHERE true ". (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")"  : "").
            implode("\n", $filterList) .  "  ORDER BY " . $sortBy . " " . $sort . " LIMIT $limit OFFSET $offset 
            ", $params);

        $modelClass = "App\\Models\\Irs";
        $model = "irs";
    
        array_map(function ($key) use ($modelClass, $model) {
            foreach ($key as $field => $value) {
                $key->class_model_name = $model;
                if ((preg_match("/file/i", $field) || preg_match("/img_/i", $field)) && !is_null($key->$field)) {
                    $url = URL::to('api/file' . "/". $modelClass::TABLE . '/' . $field . '/' . $key->id);
                    $thumbnailUrl = URL::to('api/thumbnail' . "/". $modelClass::TABLE . '/' . $field . '/' . $key->id);
                    $ext = pathinfo($key->$field, PATHINFO_EXTENSION);
                    $filename = pathinfo(storage_path($key->$field), PATHINFO_BASENAME);

                    $key->$field = (object) [
                        "ext" => (is_null($key->$field)) ? null : $ext,
                        "url" => $url,
                        "tumbnail_url" => $thumbnailUrl,
                        "filename" => (is_null($key->$field)) ? null : $filename,
                        "field_value" => $key->$field
                    ];
                }
            }
            return $key;
        }, $data);
        

        $sqlForCount = "SELECT COUNT(1) AS total FROM (" . $sql . ") as dummy WHERE true ". 
            (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")" : "") .
            implode("\n", $filterList);
        

        // Now $milestone_hierarchy contains the organized data in the desired hierarchical structure
        
        $total = DB::selectOne($sqlForCount, $params)->total;
        $modelInfo = [
            "sortable" => $sortableList,
            "filterable" => $filterableList,
            "searchable" => $searchedList,
        ];

        $totalPage = ceil($total / $limit);

        $total_ipk = 0;
        $total_sks = 0;
        $data_params = [
            "mahasiswa_id1" => $mahasiswa_id,
            "mahasiswa_id2" => $mahasiswa_id,
        ];

        $data_irs = DB::selectOne("SELECT
            ROUND((SUM(COALESCE(k.ip_semester, 0)*i.sks_semester) / SUM(i.sks_semester))::numeric, 2) as ipk,
            SUM(i.sks_semester) AS total_sks
        FROM 
            khs k
            LEFT JOIN mahasiswa m ON k.mahasiswa_id = m.id 
            LEFT JOIN irs i ON k.mahasiswa_id = m.id AND k.semester = i.semester
        WHERE 
            k.mahasiswa_id = :mahasiswa_id1 AND i.mahasiswa_id = :mahasiswa_id2
        GROUP BY 
            m.id, m.tahun_masuk", $data_params);

        if($data_irs){
            $total_ipk = $data_irs->ipk;
            $total_sks = $data_irs->total_sks;
        }

        foreach($data as $key => $value){
            $data[$key]->total_sks = $total_sks;
            $data[$key]->total_ipk = $total_ipk;
        }

        
        return [
            "success" => true,
            "total_ipk" => $total_ipk,
            "total_sks" => $total_sks,
            "data" => $data,
            "total" => $total,
            "totalPage" => $totalPage,
            "model" => $modelInfo
        ];
    }

    public function listRekapMahasiswaAngkatan(Request $request){
        $input = $request->all();

        $validation = [
            "tahun_angkatan" => "required",
            "dosen_wali_id" => "nullable"
        ];

        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return [
                "success" => false,
                "message" => $validator->errors()->first()
            ];
        }

        // check if current user is dosen wali
        $dosen_wali_id = null;
        $user_id = auth('api')->user()->id;
        $dosen_wali = DB::selectOne("
            SELECT 
                dw.id,
                dw.user_id,
                dw.name,
                dw.phone_number,
                dw.nip
            FROM dosen_wali dw
            WHERE dw.user_id = :user_id
        ", [
            "user_id" => $user_id
        ]);
        if(!is_null($dosen_wali)){
            $dosen_wali_id = $dosen_wali->id;
        }
        // dd($dosen_wali_id, $dosen_wali_id != null);
        $dosen_wali_where = $dosen_wali_id != null ? " AND m.dosen_wali_id = $dosen_wali_id " : " ";
        $params = [
            "tahun_angkatan" => $input["tahun_angkatan"]
        ];

        $list_mahasiswa = DB::select("SELECT m.name, m.nim, m.jalur_masuk, m.status, SUM(i.sks_semester) as total_sks, SUM(k.ip_semester*i.sks_semester) / SUM(i.sks_semester) as total_ipk, p.nilai as nilai_pkl, s.nilai as nilai_skripsi
        FROM mahasiswa m 
        LEFT JOIN khs k ON k.mahasiswa_id = m.id 
        LEFT JOIN irs i ON i.mahasiswa_id = m.id AND k.semester = i.semester
        LEFT JOIN pkl p ON p.mahasiswa_id = m.id
        LEFT JOIN skripsi s ON s.mahasiswa_id = m.id
        WHERE tahun_masuk = :tahun_angkatan " . $dosen_wali_where . "
        GROUP BY m.name, m.nim, m.jalur_masuk, m.status, p.nilai, s.nilai ORDER BY m.name", $params);

        // count how many has pkl data on each mahasiswa
        $pkl_statistics = (array) DB::selectOne("SELECT SUM(has_not_pkl) AS total_not_pkl, SUM(has_pkl) AS total_pkl FROM (
            SELECT 
                m.name, 
                CASE 
                WHEN p.id IS NOT NULL THEN 1 
                ELSE 0 END as has_pkl, 
                CASE WHEN p.id IS NULL THEN 1 
                ELSE 0 END as has_not_pkl
            FROM mahasiswa m 
            LEFT JOIN pkl p ON p.mahasiswa_id = m.id WHERE tahun_masuk = :tahun_angkatan " . $dosen_wali_where . "
            ) dummy", $params);

        $skripsi_statistics = (array) DB::selectOne("SELECT SUM(has_not_skripsi) AS total_not_skripsi, SUM(has_skripsi) AS total_skripsi FROM (
            SELECT 
                m.name, 
                CASE 
                WHEN s.id IS NOT NULL THEN 1 
                ELSE 0 END as has_skripsi, 
                CASE WHEN s.id IS NULL THEN 1 
                ELSE 0 END as has_not_skripsi
            FROM mahasiswa m
            LEFT JOIN skripsi s ON s.mahasiswa_id = m.id WHERE tahun_masuk = :tahun_angkatan " . $dosen_wali_where . "
            ) dummy", $params);

        $total_lulus = DB::selectOne("SELECT COUNT(m.id) as total FROM mahasiswa m WHERE m.status = 'lulus' AND tahun_masuk = :tahun_angkatan " . $dosen_wali_where . " ", $params)->total;

        $total_mahasiswa = DB::selectOne("SELECT COUNT(m.id) as total FROM mahasiswa m WHERE tahun_masuk = :tahun_angkatan " . $dosen_wali_where . " ", $params)->total;
        
        $return_data = [
            "success" => true,
            "total_mahasiswa" => $total_mahasiswa,
            "total_lulus" => $total_lulus,
            "pkl_statistics" => $pkl_statistics,
            "skripsi_statistics" => $skripsi_statistics,
            "data" => $list_mahasiswa,
        ];

        return $return_data;
        
    }
}
