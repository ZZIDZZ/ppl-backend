<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrudController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => ['auth.rest']
], function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Hello World!'], 200);
    });
    // crud routes
    Route::get('/{model}/list', [CrudController::class, 'list']);
    Route::get('/{model}/show/{id}', [CrudController::class, 'show']);
    Route::post('/{model}/create', [CrudController::class, 'create']);
    Route::put('/{model}/update/{id}', [CrudController::class, 'update']);
    Route::delete('/{model}/delete/{id}', [CrudController::class, 'delete']);
});



Route::post("/login", [AuthController::class, 'login']);
Route::get("/logout", [AuthController::class, 'logout']);