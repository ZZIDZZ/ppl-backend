<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\UploadController;
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
Route::post("/login", [AuthController::class, 'login']);
Route::get("/logout", [AuthController::class, 'logout']);


// file routes
Route::group([
], function () {
    Route::get('file/{model}/{field}/{id}', [UploadController::class, 'getFile']);
    Route::get('thumbnail/{model}/{field}/{id}', [UploadController::class, 'getTumbnailFile']);
    Route::get('temp-file/{path}', [UploadController::class, 'getTempFile']);
});


// start custom routes
Route::group([
    'middleware' => ['auth.rest']
], function () {

    // start custom routes for operator
    Route::prefix('operator')->group(
        function () {
            Route::get('download-template', [OperatorController::class, 'downloadTemplate']);
            Route::post('import-excel', [OperatorController::class, 'importExcel']);
        }
    );


});


// crud routes
Route::group([
    'middleware' => ['auth.rest']
], function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Hello World!'], 200);
    });
    Route::get('/me', [AuthController::class, 'me']);
    // crud routes
    Route::post('upload', [UploadController::class, 'upload'])->name("upload")->middleware('auth.rest');
    Route::get('/{model}/list', [CrudController::class, 'list']);
    Route::get('/{model}/show/{id}', [CrudController::class, 'show']);
    Route::post('/{model}/create', [CrudController::class, 'create']);
    Route::put('/{model}/update/{id}', [CrudController::class, 'update']);
    Route::delete('/{model}/delete/{id}', [CrudController::class, 'delete']);
});

