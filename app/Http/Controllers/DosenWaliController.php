<?php

namespace App\Http\Controllers;

use App\Models\DosenWali;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DosenWaliController extends Controller
{
    public function boot(){
        // check if user role is dosen_wali
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if(auth('api')->user()->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    protected function is_blank($array, $key)
    {
        return isset($array[$key]) ? (is_null($array[$key]) || $array[$key] === "") : true;
    }

    public function listIrsPerwalian(Request $request)
    {
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "sks_semester", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "sks_semester" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "riwayat_status_akademik_id" => ["operator" => "=", "type" => "string"],
            "semester_akademik_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;

        $sort = strtoupper($input["sort"] ?? "DESC") == "ASC" ? "ASC" : "DESC";
    
        $sortBy = "id";

        // get all request params
        $input = $request->all();

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

        
        $limit = $input["limit"] ?? 10;
        $offset = $input["offset"] ?? 0;
        if (!is_null($input["page"] ?? null)) {
            $offset = $limit * ($input["page"] - 1);
        }
        // change reminder_day_config to integer
        // Fetch data from the SQL query
        $sql = "SELECT i.id as id, 
            i.sks_semester as sks_semester,
            i.mahasiswa_id as mahasiswa_id,
            i.semester_akademik_id as semester_akademik_id,
            i.created_at as created_at, 
            i.updated_at as updated_at,
            m.nim as nim,
            m.name as nama,
            sa.tahun_ajaran as tahun_ajaran,
            sa.semester as semester,
            m.phone_number as no_telp,
            i.status_code as status_code,
            i.file_scan_irs as file_scan_irs
            FROM irs i 
            LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
            LEFT JOIN semester_akademik sa ON i.semester_akademik_id = sa.id
            WHERE m.dosen_wali_id = 1
            ";
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

    public function listKhsPerwalian(Request $request)
    {
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "ip_semester", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "ip_semester" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "riwayat_status_akademik_id" => ["operator" => "=", "type" => "string"],
            "semester_akademik_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;
        

        $sort = strtoupper($input["sort"] ?? "DESC") == "ASC" ? "ASC" : "DESC";
    
        $sortBy = "id";

        // get all request params
        $input = $request->all();

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

        
        $limit = $input["limit"] ?? 10;
        $offset = $input["offset"] ?? 0;
        if (!is_null($input["page"] ?? null)) {
            $offset = $limit * ($input["page"] - 1);
        }
        // change reminder_day_config to integer
        // Fetch data from the SQL query
        $sql = "SELECT k.id as id, 
            k.ip_semester as ip_semester,
            k.mahasiswa_id as mahasiswa_id,
            k.semester_akademik_id as semester_akademik_id,
            k.created_at as created_at, 
            k.updated_at as updated_at,
            m.nim as nim,
            m.name as nama,
            sa.tahun_ajaran as tahun_ajaran,
            sa.semester as semester,
            m.phone_number as no_telp,
            k.status_code as status_code,
            k.file_scan_khs as file_scan_khs
            FROM khs k 
            LEFT JOIN mahasiswa m ON k.mahasiswa_id = m.id
            LEFT JOIN semester_akademik sa ON k.semester_akademik_id = sa.id
            LEFT JOIN irs i ON i.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = 1
            ";
        $data = DB::select("
        SELECT * FROM (
            ". $sql .") as dummy WHERE true ". (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")"  : "").
            implode("\n", $filterList) .  "  ORDER BY " . $sortBy . " " . $sort . " LIMIT $limit OFFSET $offset 
            ", $params);

        $modelClass = "App\\Models\\Khs";
        $model = "khs";

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

    public function listPklPerwalian(Request $request)
    {
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "nilai", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code", "tanggal_selesai", "is_lulus"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "nilai" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "riwayat_status_akademik_id" => ["operator" => "=", "type" => "string"],
            "semester_akademik_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
            "tanggal_selesai" => ["operator" => "=", "type" => "string"],
            "is_lulus" => ["operator" => "=", "type" => "string"],
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;
        

        $sort = strtoupper($input["sort"] ?? "DESC") == "ASC" ? "ASC" : "DESC";
    
        $sortBy = "id";

        // get all request params
        $input = $request->all();

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

        
        $limit = $input["limit"] ?? 10;
        $offset = $input["offset"] ?? 0;
        if (!is_null($input["page"] ?? null)) {
            $offset = $limit * ($input["page"] - 1);
        }
        // change reminder_day_config to integer
        // Fetch data from the SQL query
        $sql = "SELECT p.id as id, 
            p.nilai as nilai,
            p.mahasiswa_id as mahasiswa_id,
            p.semester_akademik_id as semester_akademik_id,
            p.created_at as created_at, 
            p.updated_at as updated_at,
            m.nim as nim,
            m.name as nama,
            sa.tahun_ajaran as tahun_ajaran,
            sa.semester as semester,
            m.phone_number as no_telp,
            p.status_code as status_code,
            p.tanggal_selesai as tanggal_selesai,
            p.is_lulus as is_lulus,
            p.file_pkl as file_pkl
            FROM pkl p 
            LEFT JOIN mahasiswa m ON p.mahasiswa_id = m.id
            LEFT JOIN semester_akademik sa ON p.semester_akademik_id = sa.id
            LEFT JOIN irs i ON i.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = 1
            ";
        $data = DB::select("
        SELECT * FROM (
            ". $sql .") as dummy WHERE true ". (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")"  : "").
            implode("\n", $filterList) .  "  ORDER BY " . $sortBy . " " . $sort . " LIMIT $limit OFFSET $offset 
            ", $params);

        $modelClass = "App\\Models\\Pkl";
        $model = "pkl";

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

    public function listSkripsiPerwalian(Request $request)
    {
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "nilai", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code", "tanggal_selesai", "is_lulus"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "nilai" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "riwayat_status_akademik_id" => ["operator" => "=", "type" => "string"],
            "semester_akademik_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
            "tanggal_selesai" => ["operator" => "=", "type" => "string"],
            "is_lulus" => ["operator" => "=", "type" => "string"],
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;
        

        $sort = strtoupper($input["sort"] ?? "DESC") == "ASC" ? "ASC" : "DESC";
    
        $sortBy = "id";

        // get all request params
        $input = $request->all();

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

        
        $limit = $input["limit"] ?? 10;
        $offset = $input["offset"] ?? 0;
        if (!is_null($input["page"] ?? null)) {
            $offset = $limit * ($input["page"] - 1);
        }
        // change reminder_day_config to integer
        // Fetch data from the SQL query
        $sql = "SELECT s.id as id, 
            s.nilai as nilai,
            s.mahasiswa_id as mahasiswa_id,
            s.semester_akademik_id as semester_akademik_id,
            s.created_at as created_at, 
            s.updated_at as updated_at,
            m.nim as nim,
            m.name as nama,
            sa.tahun_ajaran as tahun_ajaran,
            sa.semester as semester,
            m.phone_number as no_telp,
            s.status_code as status_code,
            s.tanggal_selesai as tanggal_selesai,
            s.is_lulus as is_lulus,
            s.file_skripsi as file_skripsi
            FROM skripsi s 
            LEFT JOIN mahasiswa m ON s.mahasiswa_id = m.id
            LEFT JOIN semester_akademik sa ON s.semester_akademik_id = sa.id
            LEFT JOIN irs i ON i.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = 1
            ";
        $data = DB::select("
        SELECT * FROM (
            ". $sql .") as dummy WHERE true ". (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")"  : "").
            implode("\n", $filterList) .  "  ORDER BY " . $sortBy . " " . $sort . " LIMIT $limit OFFSET $offset 
            ", $params);

        $modelClass = "App\\Models\\Skripsi";
        $model = "skripsi";

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
