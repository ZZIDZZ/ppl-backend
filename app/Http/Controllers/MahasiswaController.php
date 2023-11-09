<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        // validate request
        $request->validate([
            'phone_number' => 'nullable|string',
            'email' => 'nullable|string',
            'city_id' => 'nullable|integer',
        ]);

        $mahasiswa->phone_number = $request->phone_number;
        $mahasiswa->email = $request->email;
        $mahasiswa->city_id = $request->city_id;
        $mahasiswa->file_profile = $request->file_profile;

        foreach (["file_profile"] as $item) {
            if((preg_match("/file/i", $item) or preg_match("/img_/i", $item))){
                if (isset($input[$item])){
                    if (is_null($input[$item])){
                        $mahasiswa->{$item} = null;
                    }
                    else if ($mahasiswa->{$item} !== $input[$item]) {
                        $tmpPath = $input[$item] ?? null;
                        if (!is_null($tmpPath)) {
                            if (!Storage::exists($tmpPath)) {
                                return response()->json(["message" => 'file not found at /tmp'], 422);
                            }
                            $tmpPath = $input[$item] ?? null;
        
                            $originalname = pathinfo(storage_path($tmpPath), PATHINFO_FILENAME);
                            $ext = pathinfo(storage_path($tmpPath), PATHINFO_EXTENSION);
        
                            $newPath = "/". 'mahasiswa' . "/" . $originalname . "." . $ext;
        
                            if (Storage::exists($newPath)) {
                                $id = 1;
                                $filename = pathinfo(storage_path($newPath), PATHINFO_FILENAME);
                                $ext = pathinfo(storage_path($newPath), PATHINFO_EXTENSION);
                                while (true) {
                                    $originalname = $filename . "($id)." . $ext;
                                    if (!Storage::exists("/". 'mahasiswa' . "/" . $originalname))
                                        break;
                                    $id++;
                                }
                                $newPath = "/". 'mahasiswa' . "/" . $originalname;
                            }
                            //OLD FILE DELETE
                            $oldFilePath = $input[$item];
                            Storage::delete($oldFilePath);
                            //END MOVE FILE
                            $input[$item] = $newPath;
                            Storage::move($tmpPath, $newPath);
                            //END MOVE FILE
                        } else {
                            //OLD FILE DELETE
                            $oldFilePath = $input[$item];
                            Storage::delete($oldFilePath);
                            //END MOVE FILE
                        }
                    }
                }
            }
        }


        $mahasiswa->save();

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

        $editable = [
            'name' => true,
            'phone_number' => true,
        ];

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
