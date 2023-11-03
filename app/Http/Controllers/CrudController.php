<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CrudController extends Controller
{
    
    function list(Request $request, $model){
        $modelClass = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));

        // cek if model exists
        if(!class_exists($modelClass)) {
            return response()->json(["message" => "$model not found"], 404);
        }

        $relations = $modelClass::FIELD_RELATIONS;
        $tableName = $modelClass::TABLE;
        $searchable = $modelClass::FIELD_SEARCHABLE;
        $sortable = $modelClass::FIELD_SORTABLE;
        $alias = $modelClass::FIELD_ALIAS;
        $fields = $modelClass::FIELDS;
        $fieldTypes = $modelClass::FIELD_TYPES;
        $title = $modelClass::TITLE;


        $relationJoin = "";
        $relationQuery = "";
        foreach($relations as $key => $value) {
            // keep role_id and add rel_role_id
            $linkTable = $value['linkTable'];
            $aliasTable = $value['aliasTable'];
            $linkField = $value['linkField'];
            $displayName = $value['displayName'];
            $selectFields = $value['selectFields'];
            $selectValues = $value['selectValue'];
            $selectFields = implode(", ", $selectFields);
            $relationJoin .= " LEFT JOIN $linkTable $aliasTable ON {$tableName}.$key = $aliasTable.$linkField";
            foreach($value['selectFields'] as $selectKey => $selectField) {
                $relationQuery .= ", $aliasTable.$selectField AS $selectValues[$selectKey]";
            }
        }

        $finalQuery = "SELECT {$tableName}.* $relationQuery FROM {$tableName}  $relationJoin  ";


        $totalCount = DB::selectOne("SELECT COUNT(*) as count FROM {$tableName}")->count;
        function toggleOrder($currentDirection) {
            return ($currentDirection == 'asc') ? 'desc' : 'asc';
        }
        // 

        if (null !== ($request->input('search'))) {
            $searchTerm = $request->input('search');
            $queryFilter = " TRUE OR ";
            $searchableList = [];
            foreach ($searchable as $key => $value) {
                $searchableList[] = " UPPER($value) ILIKE '%{$searchTerm}%' ";
            }
            $finalQuery = $finalQuery ." WHERE TRUE " . " AND (" . implode(" OR ", $searchableList) . ") ";
        } 
        if(null !== ($request->input('orderBy'))) {
            $orderBy = $request->input('orderBy');
            $isRelation = false;
            $queryOrderBy = $orderBy;
            foreach($relations as $key => $value) {
                if($key == $orderBy) {
                    $isRelation = true;
                    break;
                }
            }
            // check if orderBy is in fields
            if($isRelation) {
                $queryOrderBy = $value['aliasTable'] . "." . $value['linkField'];
            }else {
                $queryOrderBy = $tableName . "." . $orderBy;
            }
            
            $order = $request->input('order');
            $finalQuery = $finalQuery . " ORDER BY $queryOrderBy $order";
        }
        $page = null !== ($request->input('page')) ? intval($request->input('page')) : 1;
        $limit = null !== ($request->input('limit')) ? intval($request->input('limit')) : 10;
        $offset = ($page - 1) * $limit;
        $finalQuery = $finalQuery . " LIMIT $limit OFFSET $offset";

        $res = DB::select($finalQuery);

        // $books = DB::select('select b.*, c.name as category from books b LEFT JOIN categories c ON b.category_id = c.id');
        // return view('list.books', ['books' => $books]);
        $data = [$res, $totalCount, $limit, $page, $searchTerm ?? '', $orderBy ?? '', $order ?? ''];
        $data = $res;
        $model = [
            'relations' => $relations,
            'tableName' => $tableName,
            'searchable' => $searchable,
            'sortable' => $sortable,
            'alias' => $alias,
            'fields' => $fields,
            'fieldTypes' => $fieldTypes,
            'title' => $title,
        ];
        // return ['data' => $data, 'model' => $model];

        return [
            'data' => $data,
            'total' => $totalCount,
            'limit' => $limit,
            'page' => $page,
            'totalPage' => ceil($totalCount / $limit),
            'model' => $model,
            'success' => true,
        ];
        array_map(function ($key) use ($modelClass, $model) {
            foreach ($key as $field => $value) {
                $key->class_model_name = $model;
                if ((preg_match("/file/i", $field) || preg_match("/img_/i", $field)) && !is_null($key->$field)) {
                    $url = URL::to('api/file' . $modelClass::FILEROOT . '/' . $field . '/' . $key->id);
                    $thumbnailUrl = URL::to('api/thumbnail' . $modelClass::FILEROOT . '/' . $field . '/' . $key->id);
                    $ext = pathinfo($key->$field, PATHINFO_EXTENSION);
                    $filename = pathinfo(storage_path($key->$field), PATHINFO_BASENAME);

                    $key->$field = (object) [
                        "ext" => (is_null($key->$field)) ? null : $ext,
                        "url" => $url,
                        "tumbnail_url" => $thumbnailUrl,
                        "filename" => (is_null($key->$field)) ? null : $filename,
                        "field_value" => $key->$field
                    ];
                }
            }
            return $key;
        }, $res);
    }
    
    function show($model, $id){
        // same implementation as list
        $modelClass = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));

        $relations = $modelClass::FIELD_RELATIONS;
        $tableName = $modelClass::TABLE;
        $searchable = $modelClass::FIELD_SEARCHABLE;
        $sortable = $modelClass::FIELD_SORTABLE;
        $alias = $modelClass::FIELD_ALIAS;
        $fields = $modelClass::FIELDS;
        $fieldTypes = $modelClass::FIELD_TYPES;
        $title = $modelClass::TITLE;


        $relationJoin = "";
        $relationQuery = "";
        foreach($relations as $key => $value) {
            // keep role_id and add rel_role_id
            $linkTable = $value['linkTable'];
            $aliasTable = $value['aliasTable'];
            $linkField = $value['linkField'];
            $displayName = $value['displayName'];
            $selectFields = $value['selectFields'];
            $selectValues = $value['selectValue']; 
            $selectFields = implode(", ", $selectFields);
            $relationJoin .= " LEFT JOIN $linkTable $aliasTable ON {$tableName}.$key = $aliasTable.$linkField";
            foreach($value['selectFields'] as $selectKey => $selectField) {
                $relationQuery .= ", $aliasTable.$selectField[$selectKey] AS $selectValues[$selectKey]";
            }
        }

        $finalQuery = "SELECT {$tableName}.* $relationQuery FROM {$tableName}  $relationJoin  WHERE {$tableName}.id = $id";

        $res = DB::select($finalQuery);
        $data = $res[0];
        $model = [
            'relations' => $relations,
            'tableName' => $tableName,
            'searchable' => $searchable,
            'sortable' => $sortable,
            'alias' => $alias,
            'fields' => $fields,
            'fieldTypes' => $fieldTypes,
            'title' => $title,
        ];

        foreach ($fields as $item) {
            if ((preg_match("/file/i", $item) or preg_match("/img_/i", $item)) and !is_null($data->$item)) {
                $url = URL::to('api/file/' . $tableName . '/' . $item . '/' . $data->id);
                $tumbnailUrl = URL::to('api/tumb-file/' . $tableName . '/' . $item . '/' . $data->id);
                $ext = pathinfo($data->$item, PATHINFO_EXTENSION);
                $filename = pathinfo(storage_path($data->$item), PATHINFO_BASENAME);
                $data->$item = (object) [
                    "ext" => (is_null($data->$item)) ? null : $ext,
                    "url" => $url,
                    "tumbnail_url" => $tumbnailUrl,
                    "filename" => (is_null($data->$item)) ? null : $filename,
                    "field_value" => $data->$item
                ];
            }
        }

        return [
            'data' => $data,
            'model' => $model,
            'success' => true,
        ];
        
    }
    
    function update($model, $id, Request $request){
        $modelClass = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));

        $relations = $modelClass::FIELD_RELATIONS;
        $tableName = $modelClass::TABLE;
        $searchable = $modelClass::FIELD_SEARCHABLE;
        $sortable = $modelClass::FIELD_SORTABLE;
        $alias = $modelClass::FIELD_ALIAS;
        $fields = $modelClass::FIELDS;
        $fieldTypes = $modelClass::FIELD_TYPES;
        $title = $modelClass::TITLE;
        $validation = $modelClass::FIELD_VALIDATION;
        $fieldInputs = $modelClass::FIELD_INPUT;
        $defaultValues = $modelClass::FIELD_DEFAULT_VALUE;

        // check id exists
        $object = $modelClass::find($id);

        if(is_null($object)) {
            return response()->json(["message" => "$model not found"], 404);
        }

        // validate input
        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 422);
        }

        $input = $request->only($fieldInputs);

        if(isset($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        }


        try{
            // append input with id
            // END MOVE FILE
            foreach ($fieldInputs as $item) {
                if (array_key_exists($item, $input)) {
                    if (!(preg_match("/file/i", $item) or preg_match("/img_/i", $item))) {
                        $inputValue = $input[$item];
                        $object->{$item} = ($inputValue !== '') ? $inputValue : null;
                    }
                }
            }
            return response()->json([
                "message" => "$model updated"
            ], 200);
        } catch(\Exception $e){
            return response()->json([
                "message" => "$model cannot be updated",
                "error" => $e->getMessage()
            ], 500);
        }

        // START MOVE FILE
        foreach ($fields as $item) {
            if((preg_match("/file/i", $item) or preg_match("/img_/i", $item))){
                if (isset($input[$item])){
                    if (is_null($input[$item])){
                        $object->{$item} = null;
                    }
                    else if ($object->{$item} !== $input[$item]) {
                        $tmpPath = $input[$item] ?? null;
                        if (!is_null($tmpPath)) {
                            if (!Storage::exists($tmpPath)) {
                                return response()->json(["message" => 'file not found at /tmp'], 422);
                            }
                            $tmpPath = $input[$item] ?? null;
        
                            $originalname = pathinfo(storage_path($tmpPath), PATHINFO_FILENAME);
                            $ext = pathinfo(storage_path($tmpPath), PATHINFO_EXTENSION);
        
                            $newPath = $modelClass::FILEROOT . "/" . $originalname . "." . $ext;
        
                            if (Storage::exists($newPath)) {
                                $id = 1;
                                $filename = pathinfo(storage_path($newPath), PATHINFO_FILENAME);
                                $ext = pathinfo(storage_path($newPath), PATHINFO_EXTENSION);
                                while (true) {
                                    $originalname = $filename . "($id)." . $ext;
                                    if (!Storage::exists($modelClass::FILEROOT . "/" . $originalname))
                                        break;
                                    $id++;
                                }
                                $newPath = $modelClass::FILEROOT . "/" . $originalname;
                            }
                            //OLD FILE DELETE
                            $oldFilePath = $input[$item];
                            Storage::delete($oldFilePath);
                            //END MOVE FILE
                            $input[$item] = $newPath;
                            Storage::move($tmpPath, $newPath);
                            //END MOVE FILE
                        } else {
                            //OLD FILE DELETE
                            $oldFilePath = $input[$item];
                            Storage::delete($oldFilePath);
                            //END MOVE FILE
                        }
                    }
                }
            }
        }
        // END MOVE FILE

        $object->save();

        foreach ($fields as $item) {
            if ((preg_match("/file/i", $item) or preg_match("/img_/i", $item)) and !is_null($object->$item)) {
                $url = URL::to('api/file/' . $tableName . '/' . $item . '/' . $object->id);
                $tumbnailUrl = URL::to('api/tumb-file/' . $tableName . '/' . $item . '/' . $object->id);
                $ext = pathinfo($object->$item, PATHINFO_EXTENSION);
                $filename = pathinfo(storage_path($object->$item), PATHINFO_BASENAME);
                $object->$item = (object) [
                    "ext" => (is_null($object->$item)) ? null : $ext,
                    "url" => $url,
                    "tumbnail_url" => $tumbnailUrl,
                    "filename" => (is_null($object->$item)) ? null : $filename,
                    "field_value" => $object->$item
                ];
            }
        }
        
        
        return response()->json([
            "message" => "$model updated",
            "data" => $object,
        ], 200);
        

        // return view('update.books', ['book' => $book[0], 'categories' => $categories]);
    }
    

    
    function create($model, Request $request){
        $modelClass = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));

        $relations = $modelClass::FIELD_RELATIONS;
        $tableName = $modelClass::TABLE;
        $searchable = $modelClass::FIELD_SEARCHABLE;
        $sortable = $modelClass::FIELD_SORTABLE;
        $alias = $modelClass::FIELD_ALIAS;
        $fields = $modelClass::FIELDS;
        $fieldTypes = $modelClass::FIELD_TYPES;
        $title = $modelClass::TITLE;
        $validation = $modelClass::FIELD_VALIDATION;
        $fieldInputs = $modelClass::FIELD_INPUT;
        $defaultValues = $modelClass::FIELD_DEFAULT_VALUE;

        // validate input
        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 422);
        }

        $input = $request->only($fieldInputs);

        // check if contain field password, if yes, encrypt it
        if(isset($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        }
        $object = new $modelClass;

        
        try{
            foreach ($fieldInputs as $item) {
                if (isset($input[$item])) {
                    $inputValue = $input[$item] ?? $defaultValues[$item];
                    $object->{$item} = ($inputValue !== '') ? $inputValue : null;
                }
            }
            $object->save();
            return response()->json([
                "message" => "$model created",
                "data" => $object,
            ], 200);
        } catch(\Exception $e){
            return response()->json([
                "message" => "$model cannot be created",
                "error" => $e->getMessage()
            ], 500);
        }
        
    }
    
    function delete(Request $request, $model, $id){
        $modelClass = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));

        $relations = $modelClass::FIELD_RELATIONS;
        $tableName = $modelClass::TABLE;
        $searchable = $modelClass::FIELD_SEARCHABLE;
        $sortable = $modelClass::FIELD_SORTABLE;
        $alias = $modelClass::FIELD_ALIAS;
        $fields = $modelClass::FIELDS;
        $fieldTypes = $modelClass::FIELD_TYPES;
        $title = $modelClass::TITLE;
        $validation = $modelClass::FIELD_VALIDATION;
        $fieldInputs = $modelClass::FIELD_INPUT;

        // check id exists
        $object = $modelClass::find($id);

        if(!$object) {
            return response()->json(["message" => "$model not found"], 404);
        }
        try{
            // delete object
            $object->delete();
            //Setelah data dihapus, hapus file yang terkait

            foreach ($fields as $item) {
                if ((preg_match("/file/i", $item) or preg_match("/img_/i", $item)) and !is_null($object->$item)) {
                    $path = $object->{$item};
                    if (Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
            }
            return response()->json([
                "message" => "$model deleted"
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                "message" => "$model cannot be deleted",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    
}
