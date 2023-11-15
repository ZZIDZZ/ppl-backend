<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
