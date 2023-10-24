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
                $searchableList[] = " UPPER($value) LIKE '%{$searchTerm}%' ";
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
    
    function update($id){
        $book = DB::select('select b.*, c.name as category  from books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.id = ?', [$id]);
        $categories = DB::select('select * from categories');
        return view('update.books', ['book' => $book[0], 'categories' => $categories]);
    }
    
    function doUpdate(Request $request, $model, $id){

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

        $validator = Validator::make($request->all(), $validation);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 422);
        }

        $input = $request->only($fieldInputs);

        $update = DB::update("UPDATE {$tableName} SET " . implode(", ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($input))) . " WHERE id = ?", array_values($input), $id);

        
        

        return redirect()->route('books.list');
    }
    
    function create(){
        $categories = DB::select('select * from categories');
        return view('create.books', ['categories' => $categories]);
    }
    
    function doCreate(Request $request){
        if(null !== $request->input('title'))
            $title = $request->input('title');
        if(null !== $request->input('author'))
            $author = $request->input('author');
        if(null !== $request->input('category_id'))
            $category_id = $request->input('category_id');
        if(null !== $request->input('price'))
            $price = $request->input('price');
        if(null !== $request->input('stock'))
            $stock = $request->input('stock');
        if(null !== $request->input('isbn'))
            $isbn = $request->input('isbn');
        
        $create = DB::insert('insert into books (isbn, title, author, category_id, price, stock) values (?, ?, ?, ?, ?, ?)', [$isbn, $title, $author, $category_id, $price, $stock]);
        return redirect()->route('books.list');

    }
    
    function doDelete(Request $request, $id){
        $delete = DB::delete('delete from books where id = ?', [$id]);
        return redirect()->route('books.list');
    }
}
