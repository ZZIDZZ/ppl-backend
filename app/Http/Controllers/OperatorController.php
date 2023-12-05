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
use App\Models\DosenWali;
use App\Models\OperatorDepartemen;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class OperatorController extends Controller
{
    public function boot(){
        // check if user role is operator
        // $role_code = 'operator';
        // $role_id = Role::where('role_code', $role_code)->first()->id;

        // if(auth('api')->user()->role_id != $role_id){
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }
    }

    public function dashboard(){
        $total_mengikuti_skripsi = 0;
        $total_lulus_pkl = 0;
        $total_lulus_skripsi = 0;
        $total_mahasiswa_lulus = 0;

        $total_mahasiswa_aktif = DB::selectOne("
            SELECT
            COALESCE(COUNT(m.id), 0) as total
            FROM mahasiswa m WHERE m.status = 'Aktif'
        ")->total;

        $total_lulus_pkl = DB::selectOne("
            SELECT 
            COUNT(m.id) as total
            FROM pkl p LEFT JOIN mahasiswa m ON m.id = p.mahasiswa_id
        ")->total;


        $total_lulus_skripsi = DB::selectOne("
            SELECT 
            COUNT(m.id) as total
            FROM skripsi s LEFT JOIN mahasiswa m ON m.id = s.mahasiswa_id
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
                    LEFT JOIN khs k ON k.mahasiswa_id = i.mahasiswa_id AND k.semester = i.semester   
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
            'total_lulus_pkl' => $total_lulus_pkl,
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
        // check if user role is operator
        $role_code = 'operator';
        $role_id = Role::where('role_code', $role_code)->first()->id;
        $user = auth('api')->user();

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $operator = OperatorDepartemen::where('user_id', $user->id)->first();

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

        $operator->phone_number = $input["phone_number"];
        $operator->email = $input["email"];



        $operator->save();

        $user = User::where('id', $user->id)->first();
        // change password of user
        $user->save();

        return response()->json([
            'message' => 'success',
            'data' => $operator
        ], 200);
    }

    public function showProfile(){
        // check if user role is operator
        $user = auth('api')->user();
        // check if user role is operator
        $role_code = 'operator';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        if($user->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::select("users.*", 'operator_departemen' . ".*", "roles.*")
            ->leftjoin('roles', 'roles.id', 'users.role_id')
            ->leftjoin('operator_departemen', 'operator_departemen' . ".user_id", "users.id")
            ->where("users.id", $user->id)->first();

        $editable = ["phone_number", "email"];

        return response()->json([
            'message' => 'success',
            'data' => $user,
            'editable' => $editable
        ], 200);
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
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
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

        
        // loop through rows
        $data = [];
        $errors = [];
        $success = [];
        $success_data = [];
        $success_count = 0;
        $error_count = 0;
        $row_count = 0;
        $return_data = [];


        foreach($rows as $row){
            $row_count++;
            if($row_count == 1){
                continue;
            }
            $nim = $row[0];
            $nama_lengkap = $row[1];
            $tahun_angkatan = $row[2];
            $status = $row[3];
            $nip = $row[4];
            $jalur_masuk = $row[5];
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

            // check if jalur_masuk is empty
            if($jalur_masuk == ''){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'Jalur Masuk tidak boleh kosong',
                ];
                continue;
            }

            // check if status is empty
            if($status == ''){
                $error_count++;
                $errors[] = [
                    'row' => $row_count,
                    'message' => 'Status tidak boleh kosong',
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

            // append data
            $data = [
                'nim' => $nim,
                'nama_lengkap' => $nama_lengkap,
                'tahun_angkatan' => $tahun_angkatan,
                'status' => $status,
                'nip' => $nip,
                'jalur_masuk' => $jalur_masuk,
            ];


            try{
                // validate data
                $validation = [
                    'nim' => 'required|unique:mahasiswa,nim|unique:users,username',
                    'nama_lengkap' => 'required',
                    'tahun_angkatan' => 'required',
                    'status' => 'required',
                    'nip' => 'nullable|exists:dosen_wali,nip',
                    'jalur_masuk' => 'required',
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

                    $dosen_wali = DosenWali::where('nip', $nip)->first();

                    $dosen_wali_id = $dosen_wali->id;
                    $dosen_wali_name = $dosen_wali->name;

                    // create new user
                    $user = new User();
                    $user->username = $nim;
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
                    $mahasiswa->status = $status;
                    $mahasiswa->jalur_masuk = $jalur_masuk;
                    $mahasiswa->dosen_wali_id = $dosen_wali_id;
                    $mahasiswa->save();

                    $data_append = [
                        'user' => $user,
                        'mahasiswa' => $mahasiswa,
                    ];
                    $data_append['user']->password = '';

                    $return_data[] = $data_append;
                    // empty password

                    // send email
                    $data = [
                        'username' => $nim,
                        'password' => $password,
                    ];
                    if($data){
                        $success_data[] = [
                            'username' => $nim,
                            'password' => $password,
                            'nim' => $nim,
                            'nama_lengkap' => $nama_lengkap,
                            'tahun_angkatan' => $tahun_angkatan,
                            'status' => $status,
                            'nip' => $nip,
                            'jalur_masuk' => $jalur_masuk,
                            'dosen_wali_name' => $dosen_wali_name,
                        ];
                    }

                    // log to storage/logs/import-data-mahasiswa.log
                    Log::channel('import-data-mahasiswa')->info('Import data mahasiswa', [
                        'nim' => $nim,
                        'nama_lengkap' => $nama_lengkap,
                        'tahun_angkatan' => $tahun_angkatan,
                        'status' => $status,
                        'nip' => $nip,
                        'jalur_masuk' => $jalur_masuk,
                        'dosen_wali_name' => $dosen_wali_name,
                        'password' => $password,
                        'username' => $nim,
                    ]);

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

        // check if success_data is not empty
        if(count($success_data) > 0){
            // create excel file with login info based on success_data
            $spreadsheetImport = new Spreadsheet();
            $spreadsheetImport->setActiveSheetIndex(0);
            $worksheet = $spreadsheetImport->getActiveSheet();
            $worksheet
                ->setTitle('Login Info')
                ->setCellValue('A1', 'NIM')
                ->setCellValue('B1', 'Nama Lengkap')
                ->setCellValue('C1', 'Tahun Angkatan (2020, 2021, dst)')
                ->setCellValue('D1', 'Status (Aktif, Cuti, Mangkir, Undur Diri, Lulus, Meninggal)')
                ->setCellValue('E1', 'Jalur Masuk (SNMPTN, SBMPTN, Mandiri, Lainnya)')
                ->setCellValue('F1', 'Username')
                ->setCellValue('G1', 'Password')
                ->setCellValue('H1', 'NIP Dosen Wali')
                ->setCellValue('I1', 'Nama Dosen Wali');

            $row = 2;
            foreach($success_data as $data){
                $worksheet
                    ->setCellValue('A'.$row, $data['nim'])
                    ->setCellValue('B'.$row, $data['nama_lengkap'])
                    ->setCellValue('C'.$row, $data['tahun_angkatan'])
                    ->setCellValue('D'.$row, $data['status'])
                    ->setCellValue('E'.$row, $data['jalur_masuk'])
                    ->setCellValue('F'.$row, $data['username'])
                    ->setCellValue('G'.$row, $data['password'])
                    ->setCellValue('H'.$row, $data['nip'])
                    ->setCellValue('I'.$row, $data['dosen_wali_name']);
                $row++;
            }
            $spreadsheetImport->getActiveSheet()->setTitle("LoginInfo ". Carbon::now()->format('YmdHis'));
            $fileNameImport = "LoginInfo". Carbon::now()->format('YmdHis') .".xlsx";
            // $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheetImport);
            // create new writer for xlsx
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheetImport);
            // check if exist folder app/excel-imports, if not then create it
            if(!File::isDirectory(storage_path('app/excel-login-info'))){
                File::makeDirectory(storage_path('app/excel-login-info'), 0755, true, true);
            }
            $writer->save(storage_path('app/excel-login-info/' . $fileNameImport));
            // $headers = [
            //     'Content-Type' => 'application/vnd.ms-excel',
            //     'Content-Disposition' => 'attachment; filename='.$fileNameImport
            // ];
            // headers for xlsx
            // $headers = [
            //     'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //     'Content-Disposition' => 'attachment; filename='.$fileNameImport
            // ];
            // $headers = [
            //     'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //     'Content-Disposition' => 'attachment; filename='.$fileName
            // ];
            // delete file from storage/tmp/$file_path
            Storage::delete($file_path);
            $url_to_file = URL::to('api/file/excel-login-info/' . $fileNameImport);

            return response()->json([
                'message' => 'Import data selesai',
                'success_count' => $success_count,
                'error_count' => $error_count,
                'errors' => $errors,
                'data' => $return_data,
                'url' => $url_to_file,
            ], 200);
    
        }
        else{
            return response()->json([
                'message' => 'Import data selesai',
                'success_count' => $success_count,
                'error_count' => $error_count,
                'errors' => $errors,
                'data' => $return_data,
                'url' => null,
            ], 500);
        }

        // return response
        // $response = [
        //     'message' => 'Import data selesai',
        //     'success_count' => $success_count,
        //     'error_count' => $error_count,
        //     'errors' => $errors,
        //     'data' => $return_data,
        // ];
        
        
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
            ->setCellValue('D1', 'Status (Aktif, Cuti, Mangkir, Undur Diri, Lulus, Meninggal)')
            ->setCellValue('E1', 'NIP Dosen Wali')
            ->setCellValue('F1', 'Jalur Masuk (SNMPTN, SBMPTN, Mandiri, Lainnya)');

        $fileName = "TemplateMahasiswa.xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        // check if exist folder app/excel-templates, if not then create it
        if(!File::isDirectory(storage_path('app/excel-templates'))){
            File::makeDirectory(storage_path('app/excel-templates'), 0755, true, true);
        }
        $writer->save(storage_path('app/excel-templates/' . $fileName));
    }

    public function createMahasiswa(Request $request){
        $input = $request->all();

        $validation = [
            'nim' => 'required|unique:users,username|unique:mahasiswa,nim',
            'nama_lengkap' => 'required',
            'tahun_angkatan' => 'required|numeric',
            'status' => 'required|in:Aktif,Cuti,Mangkir,Undur Diri,Lulus,Meninggal',
            'jalur_masuk' => 'required|in:SNMPTN,SBMPTN,Mandiri,Lainnya',
            'dosen_wali_id' => 'required|exists:dosen_wali,id',
        ];

        $validator = Validator::make($input, $validation);

        if($validator->fails()){
            return response()->json($validator->errors()->all(), 400);
        }

        $role_code = 'mahasiswa';
        $role_id = Role::where('role_code', $role_code)->first()->id;


        $user = new User();
        $user->username = $input['nim'];
        // generate random 8 digit hexadecimal password
        $password = Str::random(8);
        $user->password = bcrypt($password);
        $user->role_id = $role_id;
        $user->save();

        $mahasiswa = new Mahasiswa();
        $mahasiswa->nim = $input['nim'];
        $mahasiswa->name = $input['nama_lengkap'];
        $mahasiswa->tahun_masuk = $input['tahun_angkatan'];
        $mahasiswa->user_id = $user->id;
        $mahasiswa->dosen_wali_id = $input['dosen_wali_id'];
        $mahasiswa->status = $input['status'];
        $mahasiswa->jalur_masuk = $input['jalur_masuk'];
        
        $mahasiswa->save();

        // return login info

        $data = [
            'username' => $input['nim'],
            'password' => $password,
        ];

        return response()->json([
            'message' => 'Mahasiswa berhasil ditambahkan',
            'data' => $data,
        ], 200);
    }

    public function createDosenWali(Request $request){
        $input = $request->all();

        $validation = [
            'nip' => 'required|unique:dosen_wali,nip|unique:users,username',
            'nama_lengkap' => 'required',
        ];

        $validator = Validator::make($input, $validation);

        if($validator->fails()){
            return response()->json($validator->errors()->all(), 400);
        }

        $role_code = 'dosen_wali';
        $role_id = Role::where('role_code', $role_code)->first()->id;

        $user = new User();
        $user->username = $input['nip'];
        // generate random 8 digit hexadecimal password
        $password = Str::random(8);
        $user->password = bcrypt($password);
        $user->role_id = $role_id;
        $user->save();

        $dosen_wali = new DosenWali();
        $dosen_wali->nip = $input['nip'];
        $dosen_wali->name = $input['nama_lengkap'];
        $dosen_wali->user_id = $user->id;
        $dosen_wali->save();

        // return login info

        $data = [
            'username' => $input['nip'],
            'password' => $password,
        ];

        return response()->json([
            'message' => 'Dosen Wali berhasil ditambahkan',
            'data' => $data,
        ], 200);
    }
}
