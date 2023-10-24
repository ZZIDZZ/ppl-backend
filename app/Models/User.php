<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table='users';

    const TABLE = "users";
    const TITLE = "User";
    const FIELDS = [
        "id",
        "email",
        "password",
        "role_id",
    ];

    const FIELD_INPUT = [
        "email",
        "password",
        "role_id",
    ];

    const FIELD_TYPES = [
        "id" => "Integer",
        "email" => "String",
        "password" => "String",
        "role_id" => "Integer",
    ];

    const FIELD_SORTABLE = ["id", "email", "password", "role_id"];

    const FIELD_SEARCHABLE = ["email"];

    const FIELD_ALIAS = [
        "id" => "id",
        "email" => "Email",
        "password" => "Password",
        "role_id" => "role_id",
    ];
    
    const FIELD_RELATIONS = [
        "role_id" => [
          "linkTable" => "roles",
          "aliasTable" => "A",
          "linkField" => "id",
          "displayName" => "role_name",
          "selectFields" => ["role_name"],
          "selectValue" => "role_name"
        ]
    ];

    const FIELD_VALIDATION = [
        "email" => "required|email|unique:users,email",
        "password" => "required",
        "role_id" => "required",
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
