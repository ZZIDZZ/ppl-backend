<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class MahasiswaController extends Controller
{
    public function boot(){
        // check if user role is mahasiswa
        $role_code = 'mahasiswa';
        $role_id = Role::where('code', $role_code)->first()->id;

        if(auth('api')->user()->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    public function __construct()
    {
        $this->middleware('api');
    }

    public function dashboard(){
        $total_ipk = 0;
        $total_sks = 0;
        $user_id = auth('api')->user()->id;
        $mahasiswa_id = Mahasiswa::where('user_id', $user_id)->first()->id;
        $params["mahasiswa_id"] = $mahasiswa_id;
        $data_irs = DB::selectOne("SELECT
            ROUND((SUM(COALESCE(k.ip_semester, 0)*i.sks_semester) / SUM(i.sks_semester))::numeric, 2) as ipk,
            SUM(i.sks_semester) AS total_sks
        FROM 
            irs i 
            LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id 
            LEFT JOIN khs k ON k.mahasiswa_id = m.id AND k.semester = i.semester
        WHERE 
            i.status_code = 'approved' AND k.status_code = 'approved' AND m.id = :mahasiswa_id
        GROUP BY 
            m.id, m.tahun_masuk", $params);
        if($data_irs){
            $total_ipk = $data_irs->ipk;
            $total_sks = $data_irs->total_sks;
        }

        // $last_semester_akademik_query = DB::selectOne("
        // SELECT dummy.*, sa.tahun_ajaran as tahun_ajaran,
        //     CASE WHEN sa.semester = 1 THEN 'Ganjil' ELSE 'Genap' END as semester_akademik FROM (
        //         SELECT
        //             m.id as id,
        //             m.tahun_masuk as angkatan,
        //             MAX(sa.id) as semester_akademik_id
        //         FROM
        //             irs i
        //             LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
        //             LEFT JOIN semester_akademik sa ON i.semester_akademik_id = sa.id
        //         WHERE
        //             i.status_code = 'approved' AND m.id = :mahasiswa_id
        //         GROUP BY
        //             m.id, m.tahun_masuk
        // ) as dummy LEFT JOIN semester_akademik sa ON dummy.semester_akademik_id = sa.id", $params);
        // $last_semester_akademik = [];
        
        // if($last_semester_akademik_query){
        //     $last_semester_akademik = (array)$last_semester_akademik_query;
        // }

        // $current_semester_query = DB::selectOne("
        //     SELECT
        //     m.id as id,
        //     m.tahun_masuk as angkatan,
        //     COUNT(i.id) as semester
        //         FROM
        //             irs i
        //             LEFT JOIN mahasiswa m ON i.mahasiswa_id = m.id
        //         WHERE
        //             i.status_code = 'approved' AND m.id = :mahasiswa_id
        //         GROUP BY
        //     m.id, m.tahun_masuk", $params);

        // $current_semester = 0;
        // if($current_semester_query){
        //     $current_semester = $current_semester_query->semester;
        // }

        $latest_pkl_query = DB::selectOne("
        SELECT 
            i.id as irs_id, 
            p.id as pkl_id, 
            i.semester as semester,
            p.nilai as nilai
        FROM 
            irs i LEFT JOIN pkl p ON i.mahasiswa_id = p.mahasiswa_id AND i.semester = p.semester
        WHERE 
            p.mahasiswa_id=:mahasiswa_id AND p.status_code = 'approved' AND i.status_code = 'approved'", $params);

        if($latest_pkl_query){
            $latest_pkl = (array)$latest_pkl_query;
        }

        $latest_skripsi_query = DB::selectOne("
        SELECT 
            i.id as irs_id, 
            s.id as pkl_id, 
            i.semester as semester,
            s.nilai as nilai
        FROM irs i LEFT JOIN skripsi p ON i.mahasiswa_id = s.mahasiswa_id AND i.semester = s.semester
        WHERE s.mahasiswa_id=:mahasiswa_id AND s.status_code = 'approved' AND i.status_code = 'approved'", $params);

        if($latest_skripsi_query){
            $latest_skripsi = (array)$latest_skripsi_query;
        }

        $pkl_is_lulus = false;
        $skripsi_is_lulus = false;
        if(isset($latest_pkl)){
            $pkl_is_lulus = true;
        }
        if(isset($latest_skripsi)){
            $skripsi_is_lulus = true;
        }

        $return_data = [
            "total_ipk" => $total_ipk,
            "total_sks" => $total_sks,
            // "last_semester_akademik" => $last_semester_akademik,
            // "current_semester" => $current_semester,
            "pkl_is_lulus" => $pkl_is_lulus,
            "skripsi_is_lulus" => $skripsi_is_lulus,
        ];

        if(isset($latest_pkl)){
            $return_data["latest_pkl"] = $latest_pkl;
        }
        else{
            $return_data["latest_pkl"] = null;
        }
        if(isset($latest_skripsi)){
            $return_data["latest_skripsi"] = $latest_skripsi;
        }else{
            $return_data["latest_skripsi"] = null;
        }

        return response()->json([
            'message' => 'success',
            'data' => $return_data
        ], 200);
    }

    public function editProfile(Request $request){
        // check if exist at storage app/excel-templates/TemplateMahasiswa.xlsx first, if not then create it

        // check if user role is mahasiswa
        $role_code = 'mahasiswa';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();


        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

        

        $input = $request->all();
        // validate input
        $validation = [
            'phone_number' => 'required|string',
            'email' => 'required|string',
            'city_id' => 'required|integer',
            'password' => 'required|string',
            // 'file_profile' => 'required|file|mimes:jpeg,png,jpg|max:2048',
        ];

        $input = $request->all();
        $validator = Validator::make($input, $validation);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $mahasiswa->phone_number = $input["phone_number"];
        $mahasiswa->email = $input["email"];
        $mahasiswa->city_id = $input["city_id"];
        // $mahasiswa->file_profile = $input["file_profile"];

        foreach (["file_profile"] as $item) {
            if ((preg_match("/file/i", $item) or preg_match("/img_/i", $item))){
                if(isset($input[$item]) and !is_null($input[$item])){
                    // dd($item);
                    $tmpPath = $input[$item]["path"] ?? null;
                    if (!is_null($tmpPath)) {
                        if (!Storage::exists($tmpPath)) {
                            return response()->json(["message" => 'file not found at /tmp'], 422);
                        }
                        $tmpPath = $input[$item]["path"];
                        $originalname = pathinfo(storage_path($tmpPath), PATHINFO_FILENAME);
                        $ext = pathinfo(storage_path($tmpPath), PATHINFO_EXTENSION);
        
                        $newPath = "/". "mahasiswa" . "/" . $originalname . "." . $ext;
                        //START MOVE FILE
                        if (Storage::exists($newPath)) {
                            $id = 1;
                            $filename = pathinfo(storage_path($newPath), PATHINFO_FILENAME);
                            $ext = pathinfo(storage_path($newPath), PATHINFO_EXTENSION);
                            while (true) {
                                $originalname = $filename . "($id)." . $ext;
                                if (!Storage::exists("/". "mahasiswa" . "/" . $originalname))
                                    break;
                                $id++;
                            }
                            $newPath = "/". "mahasiswa" . "/" . $originalname;
                        }
        
                        $ext = pathinfo(storage_path($newPath), PATHINFO_EXTENSION);
                        $mahasiswa->{$item} = $newPath;
                        
                        Storage::move($tmpPath, $newPath);
                    }
                }
            }
        }


        $mahasiswa->save();

        $user = User::where('id', $user->id)->first();
        // change password of user
        $user->password = bcrypt($input["password"]);
        $user->save();

        return response()->json([
            'message' => 'success',
            'data' => $mahasiswa
        ], 200);
    }

    public function showProfile(){
        // check if user role is mahasiswa
        $user = auth('api')->user();
        // check if user role is mahasiswa
        $role_code = 'mahasiswa';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::select("users.*", 'mahasiswa' . ".*", "roles.*", "cities.name as city_name")
            ->leftjoin('roles', 'roles.id', 'users.role_id')
            ->leftjoin('mahasiswa', 'mahasiswa' . ".user_id", "users.id")
            ->leftjoin('cities', 'cities.id', 'mahasiswa.city_id')
            ->where("users.id", $user->id)->first();

        foreach (["file_profile"] as $item) {
            if ((preg_match("/file/i", $item) or preg_match("/img_/i", $item)) and !is_null($user->$item)) {
                $url = URL::to('api/file/' . 'mahasiswa' . '/' . $item . '/' . $user->id);
                $tumbnailUrl = URL::to('api/tumb-file/' . 'mahasiswa' . '/' . $item . '/' . $user->id);
                $ext = pathinfo($user->$item, PATHINFO_EXTENSION);
                $filename = pathinfo(storage_path($user->$item), PATHINFO_BASENAME);
                $user->$item = (object) [
                    "ext" => (is_null($user->$item)) ? null : $ext,
                    "url" => $url,
                    "tumbnail_url" => $tumbnailUrl,
                    "filename" => (is_null($user->$item)) ? null : $filename,
                    "field_value" => $user->$item
                ];
            }
        }

        $editable = ["phone_number", "email", "city_id", "file_profile"];

        return response()->json([
            'message' => 'success',
            'data' => $user,
            'editable' => $editable
        ], 200);
    }

    public function changePassword(Request $request){
        // check if user role is mahasiswa
        $user = auth('api')->user();
        // check if user role is mahasiswa
        $role_code = 'mahasiswa';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user_mahasiswa = User::where('id', $user->id)->first();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
    
        // validate request
        $request->validate([
            'password' => 'required|string',
        ]);

        $user_mahasiswa->password = bcrypt($request->password);
        $mahasiswa->password_changed = true;
        $user_mahasiswa->save();
        $mahasiswa->save();

        return response()->json([
            'message' => 'success',
            'data' => $user_mahasiswa
        ], 200);
    }
}