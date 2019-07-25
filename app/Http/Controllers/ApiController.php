<?php

namespace App\Http\Controllers;

use App\Menu;
use App\Orders;
use App\Storage;
use ClickHouseDB\Client;
use Illuminate\Http\Request;
use Prophecy\Doubler\ClassPatch\SplFileInfoPatch;

class ApiController extends Controller {
    /* @type \ClickHouseDB\Client */
    public $db;
    private function initDB(): Client{
        if (($db = $this->db) == null){
            $db = new Client([
                'host' => env('CHDB_HOST'),
                'port' => env('CHDB_PORT'),
                'database' => env('CHDB_DATABASE'),
                'username' => env('CHDB_USERNAME'),
                'password' => env('CHDB_PASSWORD'),
            ]);
            $db->database(env('CHDB_DATABASE'));
            $db->setTimeout(10);
            $this->db = $db;
        }
        return $db;
    }
    public function uploadCover(){
        $response = [
            'success' => false
        ];
        $success = &$response['success'];
        $file = \request()->file('cover');
        if($file->getSize() <= 3145828){
            if(is_numeric(($elem_id = \request()->get('elem_id')))) {
                if(Menu::where('id', $elem_id)->first() != null){
                    $success = true;
                    $file->storePubliclyAs('public/covers', $elem_id.'.jpg');

                }else{
                    $response['error'] = "Ошибка валидации";
                }
            }
        }else{
            $response['error'] = "Размер файла не должен превышать 3 мб";
        }

        return response()->json($response);
    }
    public function main($action, $method){
        $db = $this->initDB();
        $response = [
            'success' => false
        ];
        $success = &$response['success'];
        switch ($action){
            case 'menu':
                switch ($method){
                    case 'get':
                        $success =true;
                        $response['response'] = Menu::select('id', 'position', 'title', 'price')->orderBy('position')->get();
                        break;
                    case 'getTitles':
                        $success = true;
                        $response['response'] = Menu::pluck('title','id');
                        break;
                    case 'uploadCover':
                        dump(\request());
                        $success =true;

                        break;
                    case 'add':
                        if(!is_numeric(($position = \request()->get('position')))) break;
                        if ($position > -1 && mb_strlen($title = \request()->get('title')) > 1){
                            if(!is_numeric(($id = \request()->get('id'))) && $id < 0) unset($id);
                        }else break;
                        if(!is_numeric(($price = \request()->get('price')))) break;
                        if($price < 0) break;

                        $success = true;
                            if (isset($id)){
                                Menu::where('id', $id)->update(['title'=> $title, 'position'=> $position, 'price' => $price]);
                            }else{
                                $response['id'] = Menu::insertGetId(['position' => $position, 'title'=> $title,'price' => $price]);
                            }
                        break;
                    case 'del':{
                        if(!is_numeric(($id = \request()->get('id'))) && $id < 0) break;
                        $success = true;
                        Menu::where('id', $id)->delete();
                    }

                    case 'updatePosition':
                        if(!is_array(($items = \request()->get('items')))) break;
                        foreach ($items as $position => $id){
//                            dump($id);
                            Menu::where('id', $id)->update(['position' => $position]);
                        }
                        $success = true;
                    break;
                }
                break;
            case 'storage':
                switch ($method){
                    case 'getAll':
                            $success = true;
                            $response['response'] = Storage::select('id', 'title', 'grams')->get();
                        break;
                    case 'add':
                        if(!is_numeric(($grams = \request()->get('grams')))) break;
                        if ($grams > -1 && mb_strlen($title = \request()->get('title')) > 1){
                            if(!is_numeric(($id = \request()->get('id'))) && $id < 0) unset($id);
                        }else break;

                        $success = true;
                        if (isset($id)){
                            Storage::where('id', $id)->update(['title'=> $title, 'grams'=> $grams]);
                        }else{
                            $response['id'] = Storage::insertGetId(['grams' => $grams, 'title'=> $title, 'price' => $price]);
                        }
                        $success = true;
                        break;
                    case 'del':
                        if(!is_numeric(($id = \request()->get('id'))) && $id < 0) break;
                        $success = true;
                        Storage::where('id', $id)->delete();
                        break;
                }
                break;
            case 'orders':
                switch ($method){
                    case 'getAll':
                        $response['response'] = Orders::select('id', 'items', 'stage')->get();
                        break;
                    case 'updateStage':
                        if(is_numeric(($order_id = \request()->get('order_id')))){
                            Orders::where('id', $order_id)->update(['stage' => (int)\request()->get('stage')]);
                            $success = true;
                        }
                        break;
                }
                break;
            case 'bill':
                if($method == 'pay'){
                    $items = request()->get('items');
                    if(!is_array($items)) return;
                    Orders::insert(['items' => json_encode($items)]);
                    $success = true;
                }
                break;
        }

        return response()->json($response);

        //        $stat = $db->insert('menu',
        //            [
        //                ["test", []],
        //                ["test3", []],
        //                ["test4", []],
        //            ],
        //            ['description', 'childrens',]
        //        );
        //        dump($stat);
    }
    private function refreshTable(){
        $this->initDB()->write('DROP TABLE menu');
        $this->initDB()->write('
        CREATE TABLE menu 
(
    id UInt16,
    position UInt16,
    created Date,
	title String

) ENGINE = ReplacingMergeTree(created, (id, created), 8192)
');
    }
}
