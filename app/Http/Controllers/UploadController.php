<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CoreService\CoreResponse;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
use App\CoreService\CoreException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    public function upload()
    {
        $file = request()->file('file');
        $originalname = $file->getClientOriginalName();

        if (Storage::exists("tmp/" . $originalname)) {
            $id = 1;
            $filename = pathinfo(storage_path("tmp/" . $originalname), PATHINFO_FILENAME);
            while (true) {
                $originalname = $filename . "($id)." . $file->getClientOriginalExtension();
                if (!Storage::exists("tmp/" . $originalname))
                    break;
                $id++;
            }
        }
        $path = $file->storeAs('tmp', $originalname);
        $ext = pathinfo(storage_path($path), PATHINFO_EXTENSION);

        $url = URL::to('api/temp-file/' . $originalname);
        $result = [
            "url" => $url,
            "filename" => $originalname,
            "path" => $path,
            "ext" => $ext
        ];
        return $result;
    }

    public function getTempFile($originalname){
        $data = "tmp/".$originalname;
        if (Storage::exists($data)) {
            $file = Storage::get($data);
            $type = Storage::mimeType($data);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return $response;
        } else {
            $path = "default/notfound.png";
            $file = Storage::get($path);
            $type = Storage::mimeType($path);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return $response;
        }
    }

    public function getFile($model, $field, $id)
    {
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            return response()->json(["message" => 'Not Found'], 404);

        if (!$classModel::TABLE)
            return response()->json(["message" => 'Not Found'], 404);

        $sql = "SELECT A." . $field . " FROM " . $classModel::TABLE . " A WHERE A.id = :id";
        $params = ["id" => $id];

        $fileName =  DB::selectOne($sql, $params)->$field;

        $path  = "/". $classModel::TABLE . '/';
        $data = $fileName;
        if (Storage::exists($data)) {
            $file = Storage::get($data);
            $type = Storage::mimeType($data);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return $response;
        } else {
            $path = "default/notfound.png";
            $file = Storage::get($path);
            $type = Storage::mimeType($path);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return $response;
        }
    }

    public function getTumbnailFile($model, $field, $id)
    {
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            return response()->json(["message" => 'Not Found'], 404);

        if (!$classModel::TABLE)
            return response()->json(["message" => 'Not Found'], 404);

        $sql = "SELECT A." . $field . " FROM " . $classModel::TABLE . " A WHERE A.id = :id";
        $params = ["id" => $id];

        $fileName =  DB::selectOne($sql, $params)->$field;

        $path  = "/". $classModel::TABLE . '/';
        $data = $fileName;
        if (Storage::exists($data)) {
            $file = Storage::get($data);
            $type = Storage::mimeType($data);
            
            $response = Image::make($file, 200)->resize(70, 70);
            return $response->response($type);
        } else {
            $path = "default/notfound.png";
            $file = Storage::get($path);
            $type = Storage::mimeType($path);

            $response = Image::make($file, 200)->resize(70, 70);
            return $response->response($type);
        }
    }
}
