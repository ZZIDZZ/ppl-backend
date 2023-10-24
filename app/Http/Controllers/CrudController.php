<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CrudController extends Controller
{
    
    function list(Request $request, $model){
        $modelClass = "App\\Models\\" . ucfirst($model);

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
            $selectValue = $value['selectValue'];
            $selectFields = implode(", ", $selectFields);
            $relationJoin .= " LEFT JOIN $linkTable $aliasTable ON {$tableName}.$key = $aliasTable.$linkField";
            foreach($value['selectFields'] as $selectField) {
                $relationQuery .= ", $aliasTable.$selectField AS $selectValue";
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
            if($isRelation) {
                $queryOrderBy = $value['aliasTable'] . "." . $value['linkField'];
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
    }
    
    function show($model, $id){
        // same implementation as list
        $modelClass = "App\\Models\\" . ucfirst($model);

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
            $selectValue = $value['selectValue']; 
            $selectFields = implode(", ", $selectFields);
            $relationJoin .= " LEFT JOIN $linkTable $aliasTable ON {$tableName}.$key = $aliasTable.$linkField";
            foreach($value['selectFields'] as $selectField) {
                $relationQuery .= ", $aliasTable.$selectField AS $selectValue";
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
        return [
            'data' => $data,
            'model' => $model,
            'success' => true,
        ];
        
    }
    
    function update($model, $id, Request $request){
        $modelClass = "App\\Models\\" . ucfirst($model);

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
        $res = DB::select("SELECT * FROM {$tableName} WHERE id = ?", [$id]);
        if(count($res) == 0) {
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
            $params = array_values($input);
            $params[] = $id;
            $update = DB::update("UPDATE {$tableName} SET " . implode(", ", array_map(function($key) {
                return "$key = ?";
            }, array_keys($input))) . " WHERE id = ?", $params);
    
            return response()->json([
                "message" => "$model updated"
            ], 200);
        } catch(\Exception $e){
            return response()->json([
                "message" => "$model cannot be updated",
                "error" => $e->getMessage()
            ], 500);
        }
        
        return response()->json([
            "message" => "$model updated"
        ], 200);
        

        // return view('update.books', ['book' => $book[0], 'categories' => $categories]);
    }
    

    
    function create($model, Request $request){
        $modelClass = "App\\Models\\" . ucfirst($model);

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
        
        try{
            $create = DB::insert("INSERT INTO {$tableName} (" . implode(", ", array_keys($input)) . ") VALUES (" . implode(", ", array_map(function($key) {
                return "?";
            }, array_keys($input))) . ")", array_values($input));
    
            return response()->json([
                "message" => "$model created"
            ], 200);
        } catch(\Exception $e){
            return response()->json([
                "message" => "$model cannot be created",
                "error" => $e->getMessage()
            ], 500);
        }
        
    }
    
    function delete(Request $request, $model, $id){
        $modelClass = "App\\Models\\" . ucfirst($model);

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
        $res = DB::select("SELECT * FROM {$tableName} WHERE id = ?", [$id]);
        if(count($res) == 0) {
            return response()->json(["message" => "$model not found"], 404);
        }

        try{
            $delete = DB::delete("DELETE FROM {$tableName} WHERE id = ?", [$id]);
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
