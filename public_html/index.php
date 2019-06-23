<?php
ini_set('display_errors', '1');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__.'/../vendor/autoload.php';

$app = new \Slim\App;

$app->get('/test/users', function(Request $request, Response $response, array $args){
    $arr = array();
    array_push($arr, array("error"=>false, "message"=>null));
    $tmp = array();
    array_push($tmp, array("id"=>1,"name"=>"ehsan", "family"=>"maddahi", "age"=>21));
    array_push($tmp, array("id"=>2,"name"=>"ali", "family"=>"moradi", "age"=>23));
    array_push($tmp, array("id"=>3,"name"=>"nazanin", "family"=>"farhadi", "age"=>27));
    array_push($arr, $tmp);
    $response->getBody()->write(json_encode($arr));
    return $response;
});

$app->get('/test/users/{id}', function(Request $request, Response $response, array $args){
    $arr = array();
    array_push($arr, array("error"=>false, "message"=>null));
    $tmp = array();
    array_push($tmp, array("id"=>1,"name"=>"ehsan", "family"=>"maddahi", "age"=>21));
    array_push($tmp, array("id"=>2,"name"=>"ali", "family"=>"moradi", "age"=>23));
    array_push($tmp, array("id"=>3,"name"=>"nazanin", "family"=>"farhadi", "age"=>27));
    array_push($arr, $tmp);
    $str = json_encode($arr);
    $new_arr = json_decode($str)[1];
    $arr = array();
    foreach($new_arr as $key){
        if($key['id'] == $request['id']){
            $arr[] = array("error"=>false, "message"=>null);
            $arr[] = array("id"=>$key['id'], "name"=>$key['name'], "family"=> $key['family'], "age"=>$key['age']);
        }
    }
    if(empty($arr)){
        $arr[] = array("error"=>true, "message"=>"User not found!");
    }
    $response->getBody()->write(json_encode($arr));
    return $response;

});

$app->run();

function dd($input){
    echo "<pre>";
    var_dump($input);
    echo "</pre>";
    die;
}