<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CoreException;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('setguard:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $user = User::select("users.*", "roles.role_code")->leftjoin('roles', 'roles.id', 'users.role_id')->where("username", $credentials["username"])->first();
        if (empty($user))
            return response()->json(["message" => __("message.userNotFound", ['username' => $credentials["username"]])], 422);
        // get table source from role_code
        switch ($user->role_code) {
            case "mahasiswa":
                $table = "mahasiswa";
                break;
            case "dosen_wali":
                $table = "dosen_wali";
                break;
            case "departemen":
                $table = "departemen";
                break;
            case "operator":
                $table = "operator_departemen";
                break;
            default:
                $table = "users";
                break;
        }
        $user = User::select("users.username", "users.role_id", $table . ".*", "roles.role_name", "roles.role_code")
            ->leftjoin('roles', 'roles.id', 'users.role_id')
            ->leftjoin($table, $table . ".user_id", "users.id")
            ->where("users.username", $credentials["username"])->first();
        // empty password
        $user->password = "";

        
        if ($token = auth('api')->attempt($credentials)) {
        } else {
            // return error with 401
            return response()->json(["message" => __("message.loginCredentialFalse")], 401);
        }

        return [
            "user" => $user->toArray(),
            "token" => $token,
            "message" => __("message.loginSuccess")
        ];



    }

    public function register(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            "email" => "required|email|unique:users",
            "name" => "required",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 422);
        }

        $input = $request->only('email', 'name', 'password');
        $user = new User();
        $user->email = $input["email"];
        $user->name = $input["name"];
        $user->password = bcrypt($input["password"]);
        $user->save();

        if ($token = Auth::attempt($input)) {
            return $this->respondWithToken($token);
        }
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $id = auth('api')->user()->id;
        $user = User::select("users.*", "roles.role_code")->leftjoin('roles', 'roles.id', 'users.role_id')->where("users.id", $id)->first();
        if (empty($user))
            return response()->json(["message" => __("message.userNotFound", ['id' => $id])], 422);
        // get table source from role_code
        switch ($user->role_code) {
            case "mahasiswa":
                $table = "mahasiswa";
                break;
            case "dosen":
                $table = "dosen";
                break;
            case "departemen":
                $table = "departemen";
                break;
            case "operator":
                $table = "operator";
                break;
            default:
                $table = "users";
                break;
        }
        $user = User::select("users.username", "users.role_id", $table . ".*", "roles.role_name", "roles.role_code")
            ->leftjoin('roles', 'roles.id', 'users.role_id')
            ->leftjoin($table, $table . ".user_id", "users.id")
            ->where("users.id", $id)->first();
        // empty password

        return response()->json([
            "data" => $user->toArray()
        ]);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // check if already logged in first
        if (!$this->guard()->check()) {
            return response()->json(["message" => __("message.notLoggedIn")], 401);
        }
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard("api");
    }
}
