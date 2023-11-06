<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Mail\SendLoginInfo;
use App\Jobs\SendLoginInfoJob;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OperatorController extends Controller
{
    public function boot(){
        // check if user role is operator
        $role_code = 'operator';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if(auth('api')->user()->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    public function downloadTemplate(){
        // check if exist at storage app/excel-templates/TemplateMahasiswa.xlsx first, if not then create it
        $fileName = "TemplateMahasiswa.xlsx";

        if(!Storage::exists(storage_path('app/excel-templates/TemplateMahasiswa.xlsx'))){
            $this->createTemplate();
        }

        /* Return Values */
        $data = [
            "filename" => $fileName,
        ];
        $fileName = $data["filename"];
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename='.$fileName
        ];
        // application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        return response()->download(storage_path('app/excel-templates/' . $fileName), $fileName, $headers);

    }

    public function importExcel(Request $request){
        $this->boot();


        $file_path = $request->file["path"];
        $fileName = $request->file["filename"];
        // get file in storage/tmp/$file_path
        $file = Storage::get($file_path);
        $file_path = Storage::path($file_path);

        if(!File::isDirectory(storage_path('app/excel-imports'))){
            File::makeDirectory(storage_path('app/excel-imports'), 0755, true, true);
        }
        $file_extension = pathinfo($fileName, PATHINFO_EXTENSION);
        // check file type for reader from extension
        if($file_extension == 'xlsx'){
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
        }else if($file_extension == 'xls'){
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
            $reader->setReadDataOnly(true);
        }else if($file_extension == 'csv') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
        }else{
            return response()->json(['message' => 'File is not valid'], 400);
        } 

        /**  Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = $reader->load($file_path);

        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);


        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // check if file is empty
        if(count($rows) <= 0){
            return response()->json(['message' => 'File is empty'], 400);
        }

        // check if file is not valid
        if($rows[0][0] != 'NIM' || $rows[0][1] != 'Nama Lengkap' || $rows[0][2] != 'Tahun Angkatan (2020, 2021, dst)' || $rows[0][3] != 'Status (AKTIF / NON-AKTIF)' || $rows[0][4] != 'Email'){
            return response()->json(['message' => 'File is not valid'], 400);
        }

        // check if file is not valid
        if($rows[1][0] == '' || $rows[1][1] == '' || $rows[1][2] == '' || $rows[1][3] == '' || $rows[1][4] == ''){
            return response()->json(['message' => 'File is not valid'], 400);
        }
        
        // loop through rows
        $data = [];
        $errors = [];
        $success = [];
        $success_count = 0;
        $error_count = 0;
        $row_count = 0;

        foreach($rows as $row){
            $row_count++;
            if($row_count == 1){
                continue;
            }
            $nim = $row[0];
            $nama_lengkap = $row[1];
            $tahun_angkatan = $row[2];
            $status = $row[3];
            $email = $row[4];
            // dd($nim, $nama_lengkap, $tahun_angkatan, $status, $email);


            // check if nim is empty
            if($nim == ''){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'NIM tidak boleh kosong',
                ];
                continue;
            }

            // check if email is empty
            if($email == ''){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'Email tidak boleh kosong',
                ];
                continue;
            }

            // check if nama_lengkap is empty
            if($nama_lengkap == ''){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'Nama tidak boleh kosong',
                ];
                continue;
            }

            // check if tahun_angkatan is empty
            if($tahun_angkatan == ''){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'Tahun Angkatan tidak boleh kosong',
                ];
                continue;
            }

            // check if nim is not valid
            if(strlen($nim) != 14){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'NIM harus 14 digit',
                ];
                continue;
            }

            // append data
            $data = [
                'nim' => $nim,
                'nama_lengkap' => $nama_lengkap,
                'tahun_angkatan' => $tahun_angkatan,
                'status' => $status,
                'email' => $email,
            ];

            try{
                // validate data
                $validation = [
                    'nim' => 'required|unique:mahasiswa',
                    'nama_lengkap' => 'required',
                    'tahun_angkatan' => 'required',
                    'status' => 'required',
                    'email' => 'required|unique:users,email',
                ];

                $validator = Validator::make($data, $validation);

                if($validator->fails()){
                    $error_count++;
                    $errors[] = [
                        'row' => $row_count,
                        'message' => $validator->errors()->all(),
                    ];
                    continue;
                }else{
                    // check role_id of role_code mahasiswa
                    $role_code = 'mahasiswa';
                    $role_id = Role::where('role_code', $role_code)->first()->id;

                    // create new user
                    $user = new User();
                    $user->email = $email;
                    // generate random 8 digit hexadecimal password
                    $password = Str::random(8);
                    $user->password = bcrypt($password);
                    $user->role_id = $role_id;
                    $user->save();

                    // create new mahasiswa
                    $mahasiswa = new Mahasiswa();
                    $mahasiswa->nim = $nim;
                    $mahasiswa->name = $nama_lengkap;
                    $mahasiswa->tahun_masuk = $tahun_angkatan;
                    $mahasiswa->user_id = $user->id;
                    $mahasiswa->status = $status == 'AKTIF' ? 1 : 0;
                    $mahasiswa->save();

                    // send email
                    $data = [
                        'email' => $email,
                        'password' => $password,
                    ];
                    SendLoginInfoJob::dispatch($data);
                    Log::info("request login " . $data['email'] . " with password " . $data['password']);
                }
            } catch(Exception $e){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => $e->getMessage(),
                ];
                continue;
            }
        }

        // return response
        $response = [
            'message' => 'Import data selesai',
            'success_count' => $success_count,
            'error_count' => $error_count,
            'errors' => $errors,
        ];
        return response()->json($response, 200);

        
    }

    protected function createTemplate(){
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet
            ->setTitle('Template Import Data Mahasiswa')
            ->setCellValue('A1', 'NIM')
            ->setCellValue('B1', 'Nama Lengkap')
            ->setCellValue('C1', 'Tahun Angkatan (2020, 2021, dst)')
            ->setCellValue('D1', 'Status (AKTIF / NON-AKTIF)')
            ->setCellValue('E1', 'Email');

        $fileName = "TemplateMahasiswa.xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        // check if exist folder app/excel-templates, if not then create it
        if(!File::isDirectory(storage_path('app/excel-templates'))){
            File::makeDirectory(storage_path('app/excel-templates'), 0755, true, true);
        }
        $writer->save(storage_path('app/excel-templates/' . $fileName));
    }
}
