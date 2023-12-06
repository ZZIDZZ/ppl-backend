<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\DosenWaliController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\DepartemenController;
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
    'middleware' => ['auth.rest'],
    'prefix' => 'custom'
], function () {
    // start custom routes for operator
    Route::prefix('operator')->group(
        function () {
            Route::post('import-excel', [OperatorController::class, 'importExcel']);
            Route::post('create-mahasiswa', [OperatorController::class, 'createMahasiswa']);
            Route::post('create-dosen-wali', [OperatorController::class, 'createDosenWali']);
            Route::post('edit-profile', [OperatorController::class, 'editProfile']);
            Route::get('show-profile', [OperatorController::class, 'showProfile']);
            Route::get('dashboard', [OperatorController::class, 'dashboard']);
        }
    );
    
    // start custom routes for mahasiswa
    Route::prefix('mahasiswa')->group(
        function () {
            Route::post('edit-profile', [MahasiswaController::class, 'editProfile']);
            Route::get('show-profile', [MahasiswaController::class, 'showProfile']);
            Route::post('change-password', [MahasiswaController::class, 'changePassword']);
            Route::get('dashboard', [MahasiswaController::class, 'dashboard']);
        }
    );

    // start custom routes for dosen_wali
    Route::prefix('dosen_wali')->group(
        function () {
            Route::get('irs/list', [DosenWaliController::class, 'listIrsPerwalian']);
            Route::get('khs/list', [DosenWaliController::class, 'listKhsPerwalian']);
            Route::get('pkl/list', [DosenWaliController::class, 'listPklPerwalian']);
            Route::get('skripsi/list', [DosenWaliController::class, 'listSkripsiPerwalian']);
            Route::get('verifikasi/{akademik}/{id}', [DosenWaliController::class, 'verifikasi']);
            Route::post('edit-profile', [DosenWaliController::class, 'editProfile']);
            Route::get('show-profile', [DosenWaliController::class, 'showProfile']);
            Route::get('dashboard', [DosenWaliController::class, 'dashboard']);
        }
    );

    // start custom routes for departemen
    Route::prefix('departemen')->group(
        function () {
            Route::post('edit-profile', [DepartemenController::class, 'editProfile']);
            Route::get('show-profile', [DepartemenController::class, 'showProfile']);
            Route::get('dashboard', [DepartemenController::class, 'dashboard']);
        }
    );

    // start custom routes for rekap
    Route::prefix('rekap')->group(
        function () {
            Route::get('list-semester-mahasiswa', [RekapController::class, 'listSemesterMahasiswa']);
            Route::get('rekap-pkl-angkatan', [RekapController::class, 'rekapPklAngkatan']);
            Route::get('list-pkl-angkatan', [RekapController::class, 'listPklAngkatan']);
            Route::get('rekap-skripsi-angkatan', [RekapController::class, 'rekapSkripsiAngkatan']);
            Route::get('list-skripsi-angkatan', [RekapController::class, 'listSkripsiAngkatan']);
            Route::get('rekap-angkatan', [RekapController::class, 'listRekapMahasiswaAngkatan']);
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

Route::get('custom/operator/download-template', [OperatorController::class, 'downloadTemplate']);


