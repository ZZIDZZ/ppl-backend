<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class RekapController extends Controller
{
    public function listSemesterMahasiswa(Request $request){
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
        $sql = "SELECT semester.semester, 
            CASE 
            WHEN k.id IS NOT NULL THEN true 
            ELSE false END as is_khs, 
            CASE 
            WHEN p.id IS NOT NULL THEN true 
            ELSE false END as is_pkl,
            CASE 
            WHEN s.id IS NOT NULL THEN true 
            ELSE false END as is_skripsi,
            irs_mahasiswa.*, sa.tahun_ajaran as tahun_ajaran, sa.semester as semester_akademik
            FROM generate_series(1, 14) AS semester
            LEFT JOIN 
            (
                SELECT irs.*, ROW_NUMBER() OVER (ORDER BY id) as irs_number FROM irs WHERE mahasiswa_id=1 ORDER BY id
            ) irs_mahasiswa 
            ON irs_mahasiswa.irs_number = semester.semester
            LEFT JOIN semester_akademik sa ON sa.id = irs_mahasiswa.semester_akademik_id
            LEFT JOIN khs k ON k.irs_id = irs_mahasiswa.id
            LEFT JOIN pkl p ON p.irs_id = irs_mahasiswa.id
            LEFT JOIN skripsi s ON s.irs_id = irs_mahasiswa.id
            ORDER BY semester.semester
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
}
