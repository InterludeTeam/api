<?php

use Illuminate\Http\Request;

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

//Route::options('/method/{action?}.{method?}', function() { return; })->middleware('cors');
Route::get('/method/{action?}.{method?}', 'ApiController@main');
Route::post('/method/uploadCover', 'ApiController@uploadCover');
Route::get('/getCoverById', function (){
   if(is_numeric(($id = \request('elem_id')))){
   }else{
       $id = "breakfast";
   }
    try{
        return response(\Illuminate\Support\Facades\Storage::get('public/covers/'.$id.'.jpg'))->header('Content-Type', 'image/jpeg');
    }catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $exp){
        return response(\Illuminate\Support\Facades\Storage::get('public/covers/breakfast.jpg'))->header('Content-Type', 'image/jpeg');
    }

});