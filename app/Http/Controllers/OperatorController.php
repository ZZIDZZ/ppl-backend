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

class OperatorController extends Controller
{
    public function boot(){
        // check if user role is operator
        $role_code = 'operator';
        $role_id = Role::where('code', $role_code)->first()->id;

        if(auth()->user()->role_id != $role_id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    public function downloadTemplate(){
        // check if exist at storage app/excel-templates/TemplateMahasiswa.xlsx first, if not then create it
        $this->boot();
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
            ->setCellValue('D1', 'Status (AKTIF / NON-AKTIF)');

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
