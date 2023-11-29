<?php

namespace App\Http\Controllers;

use App\Models\DosenWali;
use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    public function dashboard(){
        $total_mengikuti_skripsi = 0;
        $total_lulus_skripsi = 0;
        $total_mahasiswa_lulus = 0;
        $user_id = auth('api')->user()->id;
        $dosen_wali_id = DosenWali::where('user_id', $user_id)->first()->id;

        $params = ["dosen_wali_id" => $dosen_wali_id];

        $total_mahasiswa_aktif = DB::selectOne("
            SELECT
            COALESCE(COUNT(m.id), 0) as total
            FROM mahasiswa m WHERE m.status = 'Aktif' AND m.dosen_wali_id = :dosen_wali_id
        ", $params)->total;

        $total_mengikuti_skripsi = DB::selectOne("
            SELECT
            COUNT(m.id) as total
            FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
            WHERE s.is_selesai = false AND m.dosen_wali_id = :dosen_wali_id
        ", $params)->total;

        $total_lulus_skripsi = DB::selectOne("
            SELECT 
            COUNT(m.id) as total
            FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
            WHERE s.is_selesai = true AND s.is_lulus = true AND m.dosen_wali_id = :dosen_wali_id
        ", $params)->total;

        $range_ipk_mahasiswa = DB::select("
        WITH rentang_ipk AS (
            SELECT
                '0.0-0.5' AS ipk_range
            UNION SELECT '0.5-1.0'
            UNION SELECT '1.0-1.5'
            UNION SELECT '1.5-2.0'
            UNION SELECT '2.0-2.5'
            UNION SELECT '2.5-3.0'
            UNION SELECT '3.0-3.5'
            UNION SELECT '3.5-4.0'
        )
        SELECT
            ri.ipk_range,
            COALESCE(COUNT(outer_query.ipk_range), 0) as jumlah_mahasiswa
        FROM
            rentang_ipk ri
        LEFT JOIN (
            SELECT
                CASE 
                    WHEN ipk >= 0.0 AND ipk < 0.5 THEN '0.0-0.5'
                    WHEN ipk >= 0.5 AND ipk < 1.0 THEN '0.5-1.0'
                    WHEN ipk >= 1.0 AND ipk < 1.5 THEN '1.0-1.5'
                    WHEN ipk >= 1.5 AND ipk < 2.0 THEN '1.5-2.0'
                    WHEN ipk >= 2.0 AND ipk < 2.5 THEN '2.0-2.5'
                    WHEN ipk >= 2.5 AND ipk < 3.0 THEN '2.5-3.0'
                    WHEN ipk >= 3.0 AND ipk < 3.5 THEN '3.0-3.5'
                    WHEN ipk >= 3.5 AND ipk <= 4.0 THEN '3.5-4.0'
                END as ipk_range
            FROM (
                SELECT
                    m.id as id,
                    m.tahun_masuk as angkatan,
                    SUM(k.ip_semester*i.sks_semester) / SUM(i.sks_semester) as ipk
                FROM 
                    irs i 
                    LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id 
                    LEFT JOIN khs k ON k.irs_id = i.id  
                WHERE 
                    i.status_code = 'approved' AND k.status_code = 'approved' AND m.dosen_wali_id = :dosen_wali_id
                GROUP BY 
                    m.id, m.tahun_masuk
            ) as inner_query
        ) as outer_query ON ri.ipk_range = outer_query.ipk_range
        GROUP BY 
            ri.ipk_range
        ORDER BY 
            ri.ipk_range;
            ", $params);

        $return_data = [
            'total_mahasiswa_aktif' => $total_mahasiswa_aktif,
            'total_mengikuti_skripsi' => $total_mengikuti_skripsi,
            'total_lulus_skripsi' => $total_lulus_skripsi,
            'total_mahasiswa_lulus' => $total_mahasiswa_lulus,
            'range_ipk_mahasiswa' => $range_ipk_mahasiswa
        ];
        return response()->json([
            'message' => 'success',
            'data' => $return_data
        ], 200);
    }
    

    public function verifikasi($akademik, $id){
        $this->boot();
        $validation = [
            "akademik" => "required",
            "id" => "required",
        ];
        $validator = Validator::make([
            "akademik" => $akademik,
            "id" => $id,
        ], $validation);
        if ($validator->fails()) {
            return [
                "success" => false,
                "message" => $validator->errors()->first()
            ];
        }

        $upload_field = ["file_scan_irs", "file_scan_khs", "file_pkl", "file_skripsi"];
        
        $user_id = auth('api')->user()->id;
        $akademikList = ["irs", "khs", "pkl", "skripsi"];
        if(!in_array($akademik, $akademikList)){
            return [
                "success" => false,
                "message" => "akademik is not valid"
            ];
        }
        $akademikClass = "\\App\\Models\\" . Str::ucfirst(Str::camel($akademik));
        // find akademik if exist
        $model = $akademikClass::find($id);
        if(is_null($model)){
            return [
                "success" => false,
                "message" => "akademik is not found"
            ];
        }
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;

        $model->status_code = 'approved';
        $model->save();

        foreach ($upload_field as $item) {
            if ((preg_match("/file/i", $item) or preg_match("/img_/i", $item)) and !is_null($model->$item)) {
                $url = URL::to('api/file/' . $akademik . '/' . $item . '/' . $model->id);
                $tumbnailUrl = URL::to('api/tumb-file/' . $akademik . '/' . $item . '/' . $model->id);
                $ext = pathinfo($model->$item, PATHINFO_EXTENSION);
                $filename = pathinfo(storage_path($model->$item), PATHINFO_BASENAME);
                $model->$item = (object) [
                    "ext" => (is_null($model->$item)) ? null : $ext,
                    "url" => $url,
                    "tumbnail_url" => $tumbnailUrl,
                    "filename" => (is_null($model->$item)) ? null : $filename,
                    "field_value" => $model->$item
                ];
            }
        }
        // if skripsi, and is_lulus is true, then update mahasiswa status to lulus
        if($akademik == 'skripsi' && $model->is_lulus == true){
            $mahasiswa = Mahasiswa::find($model->mahasiswa_id);
            $mahasiswa->status = 'Lulus';
            $mahasiswa->save();
        }

        return [
            "success" => true,
            "message" => "Berhasil verifikasi",
            "data" => $model,
        ];
    }

    public function editProfile(Request $request){
        // check if user role is dosen_wali
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();
        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $dosen_wali = DosenWali::where('user_id', $user->id)->first();

        $input = $request->all();

        $validation = [
            'phone_number' => 'nullable|string',
            'email' => 'nullable|string',
        ];

        $input = $request->all();
        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $dosen_wali->phone_number = $input["phone_number"];
        $dosen_wali->email = $input["email"];



        $dosen_wali->save();

        $user = User::where('id', $user->id)->first();
        // change password of user
        $user->save();

        return response()->json([
            'message' => 'success',
            'data' => $dosen_wali
        ], 200);
    }

    public function showProfile(){
        // check if user role is dosen_wali
        $user = auth('api')->user();
        // check if user role is dosen_wali
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::select("users.*", 'dosen_wali' . ".*", "roles.*")
            ->leftjoin('roles', 'roles.id', 'users.role_id')
            ->leftjoin('dosen_wali', 'dosen_wali' . ".user_id", "users.id")
            ->where("users.id", $user->id)->first();

        $editable = ["phone_number", "email"];

        return response()->json([
            'message' => 'success',
            'data' => $user,
            'editable' => $editable
        ], 200);
    }

    public function listIrsPerwalian(Request $request)
    {
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();
        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "sks_semester", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "sks_semester" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"]
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;
        $input = $request->all();


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
            i.semester as semester,
            m.nim as nim,
            m.name as nama,
            m.phone_number as no_telp,
            i.status_code as status_code,
            i.file_scan_irs as file_scan_irs
            FROM irs i 
            LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = :dosen_wali_id AND i.status_code='waiting_approval'
            ";
        $params["dosen_wali_id"] = $dosen_wali_id;
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
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();
        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "ip_semester", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "ip_semester" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"]
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;

        $input = $request->all();
        

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
            k.semester as semester,
            m.nim as nim,
            m.name as nama,
            m.phone_number as no_telp,
            k.status_code as status_code,
            k.file_scan_khs as file_scan_khs
            FROM khs k 
            LEFT JOIN mahasiswa m ON k.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = :dosen_wali_id AND k.status_code='waiting_approval'
            ";
        $params["dosen_wali_id"] = $dosen_wali_id;

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
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();
        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "nilai", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code", "tanggal_selesai", "is_lulus"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "nilai" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"]
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;
        $input = $request->all();

        

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
            p.semester as semester,
            m.nim as nim,
            m.name as nama,
            m.phone_number as no_telp,
            p.status_code as status_code,
            p.tanggal_selesai as tanggal_selesai,
            p.is_lulus as is_lulus,
            p.file_pkl as file_pkl
            FROM pkl p 
            LEFT JOIN mahasiswa m ON p.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = :dosen_wali_id AND p.status_code='waiting_approval'
            ";
        $params["dosen_wali_id"] = $dosen_wali_id;
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
        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();
        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Initialize an empty array to hold the milestone hierarchy data
        $milestone_hierarchy = [];
        $searchedList = ["nim", "nama", "no_telp", "status_code"];
        $sortableList = ["id", "nilai", "mahasiswa_id", "riwayat_status_akademik_id", "semester_akademik_id", "created_at", "updated_at", "nim", "nama", "tahun_ajaran", "semester", "no_telp", "status_code", "tanggal_selesai", "is_lulus"];
        $filterableList = [
            "id" => ["operator" => "=", "type" => "string"],
            "nilai" => ["operator" => "=", "type" => "string"],
            "mahasiswa_id" => ["operator" => "=", "type" => "string"],
            "created_at" => ["operator" => "=", "type" => "string"],
            "updated_at" => ["operator" => "=", "type" => "string"],
            "nim" => ["operator" => "=", "type" => "string"],
            "nama" => ["operator" => "=", "type" => "string"],
            "tahun_ajaran" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"],
            "no_telp" => ["operator" => "=", "type" => "string"],
            "status_code" => ["operator" => "=", "type" => "string"],
            "semester" => ["operator" => "=", "type" => "string"]
        ];
        $params = [];
        $user_id = auth('api')->user()->id;
        $dosen_wali = DosenWali::where('user_id', $user_id)->first();
        $dosen_wali_id = $dosen_wali->id;
        $input = $request->all();

        

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
            s.semester as semester,
            m.nim as nim,
            m.name as nama,
            m.phone_number as no_telp,
            s.status_code as status_code,
            s.tanggal_selesai as tanggal_selesai,
            s.is_lulus as is_lulus,
            s.file_skripsi as file_skripsi
            FROM skripsi s 
            LEFT JOIN mahasiswa m ON s.mahasiswa_id = m.id
            WHERE m.dosen_wali_id = :dosen_wali_id AND s.status_code='waiting_approval'
            ";
        $params["dosen_wali_id"] = $dosen_wali_id;
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

    // public function verifikasi(Request $request, $id, $akademik){
    //     $akademik_table = '';
    //     if($akademik == 'irs'){
    //         $akademik_table = 'irs';
    //     }else if ($akademik == 'khs'){
    //         $akademik_table = 'khs';
    //     }else if ($akademik == 'pkl'){
    //         $akademik_table = 'pkl';
    //     }else if ($akademik == 'skripsi'){
    //         $akademik_table = 'skripsi';
    //     }
    //     else{
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'akademik tidak ditemukan'
    //         ], 400);
    //     }

    //     $akademik_model = 'App\\Models\\' . ucfirst($akademik);
    //     $akademik = $akademik_model::where('id', $id)->first();
    // }
}
