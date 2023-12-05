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

        $query = DB::select("
        SELECT 
            t.tahun_masuk,
            COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
            COALESCE(r.belum_lulus, 0) AS belum_lulus
        FROM 
            (SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC LIMIT 7) t
        LEFT JOIN (
            SELECT 
                m.tahun_masuk,
                SUM(CASE WHEN COALESCE(p.nilai, 'X') = 'A' THEN 1
                WHEN COALESCE(p.nilai, 'X') = 'B' THEN 1
                WHEN COALESCE(p.nilai, 'X') = 'C' THEN 1 ELSE 0 END) AS sudah_lulus,
                SUM(CASE WHEN COALESCE(p.nilai, 'X') = 'X' AND p.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
            FROM 
                mahasiswa m
            LEFT JOIN 
                pkl p ON p.mahasiswa_id = m.id
            ". $dosen_wali_where ."
            GROUP BY 
                m.tahun_masuk
        ) r ON t.tahun_masuk = r.tahun_masuk
        ORDER BY 
            t.tahun_masuk DESC
        ");

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

        $query = DB::select("
        SELECT 
            t.tahun_masuk,
            COALESCE(r.sudah_lulus, 0) AS sudah_lulus,
            COALESCE(r.belum_lulus, 0) AS belum_lulus
        FROM 
            (SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC LIMIT 7) t
        LEFT JOIN (
            SELECT 
                m.tahun_masuk,
                SUM(CASE WHEN COALESCE(s.nilai, 'X') = 'A' THEN 1
                WHEN COALESCE(s.nilai, 'X') = 'B' THEN 1
                WHEN COALESCE(s.nilai, 'X') = 'C' THEN 1 ELSE 0 END) AS sudah_lulus,
                SUM(CASE WHEN COALESCE(s.nilai, 'X') = 'X' AND s.id IS NULL THEN 1 ELSE 0 END) AS belum_lulus
            FROM 
                mahasiswa m
            LEFT JOIN 
                skripsi s ON s.mahasiswa_id = m.id
            ". $dosen_wali_where ."
            GROUP BY 
                m.tahun_masuk
        ) r ON t.tahun_masuk = r.tahun_masuk
        ORDER BY 
            t.tahun_masuk DESC
        ");

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
            s.nilai as nilai_skripsi
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
        return [
            "success" => true,
            "data" => $data,
            "total" => $total,
            "totalPage" => $totalPage,
            "model" => $modelInfo
        ];
    }
}
