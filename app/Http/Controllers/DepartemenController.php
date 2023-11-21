<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\OperatorDepartemen;
use App\Models\Departemen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartemenController extends Controller
{
    public function boot(){
        // check if user role is departemen
        $role_code = 'departemen';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if(auth('api')->user()->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    public function dashboard(){
        $total_mengikuti_skripsi = 0;
        $total_lulus_skripsi = 0;
        $total_mahasiswa_lulus = 0;

        $total_mahasiswa_aktif = DB::selectOne("
            SELECT
            COALESCE(COUNT(m.id), 0) as total
            FROM mahasiswa m WHERE m.status = 'Aktif'
        ")->total;

        $total_mengikuti_skripsi = DB::selectOne("
            SELECT
            COUNT(m.id) as total
            FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
            WHERE s.is_selesai = false
        ")->total;

        $total_lulus_skripsi = DB::selectOne("
            SELECT 
            COUNT(m.id) as total
            FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
            WHERE s.is_selesai = true AND s.is_lulus = true 
        ")->total;

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
                    i.status_code = 'approved' AND k.status_code = 'approved'
                GROUP BY 
                    m.id, m.tahun_masuk
            ) as inner_query
        ) as outer_query ON ri.ipk_range = outer_query.ipk_range
        GROUP BY 
            ri.ipk_range
        ORDER BY 
            ri.ipk_range;
            ");

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

    public function editProfile(Request $request){
        // check if user role is departemen
        $role_code = 'departemen';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $departemen = Departemen::where('user_id', $user->id)->first();

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

        $departemen->phone_number = $input["phone_number"];
        $departemen->email = $input["email"];



        $departemen->save();

        $user = User::where('id', $user->id)->first();
        // change password of user
        $user->save();

        return response()->json([
            'message' => 'success',
            'data' => $departemen
        ], 200);
    }

    public function showProfile(){
        // check if user role is departemen
        $user = auth('api')->user();
        // check if user role is departemen
        $role_code = 'departemen';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::select("users.*", 'departemen' . ".*", "roles.*")
            ->leftjoin('roles', 'roles.id', 'users.role_id')
            ->leftjoin('departemen', 'departemen' . ".user_id", "users.id")
            ->where("users.id", $user->id)->first();

        $editable = ["phone_number", "email"];

        return response()->json([
            'message' => 'success',
            'data' => $user,
            'editable' => $editable
        ], 200);
    }
}
