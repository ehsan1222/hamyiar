<?php
ini_set('display_errors', 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Base\Config\Database;
use Base\Config\UserDatabase;

require_once __DIR__.'/../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$app->post('/register', function(Request $request, Response $response, array $args){
    //get data from HTTP POST
    $data = $request->getParsedBody();
    
    $output = array();
    
    if(empty($data['name_family'])){
        $output = [
            ["error"=> true , "message"=>"name_family parameter isn't set"]
        ];
    } else if(empty($data['email'])){
        $output = [
            ["error"=> true, "message"=>"email parameter isn't set"]
        ];
    } else if(empty($data['username'])){
        $output = [
            ["error"=> true, "message"=>"username parameter isn't set"]
        ];
    } else if(empty($data['email'])){
        $output = [
            ["error"=> true, "message"=>"password parameter isn't set"]
        ];
    } else {
        $name_family = validate_data($data['name_family']);
        $email       = validate_data($data['email']);
        $username    = validate_data($data['username']);
        $password    = validate_data($data['password']);
        
        if(!preg_match('/^[a-zA-Z0-9]{5,}$/', $username)) { // for english chars + numbers only
            // valid username, alphanumeric & longer than or equals 5 chars
            $output = [
                ["error"=> true, "message"=>"username isn't valid"]
            ];  
        } else if(!preg_match('/^[a-zA-Z0-9]{5,}$/', $password)){
            $output = [
                ["error"=> true, "message"=>"password isn't valid"]
            ];
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $output = [
                ["error"=> true, "message"=>"invalid email format"]
            ]; 
        }else{
            // store validate value in array
            $arr = [
                "name_family" => $name_family,
                "email"       => $email,
                "username"    => $username,
                "password"    => $password
            ];
    
            // connect to database
            $connection    = new Database();
            $user_database = new UserDatabase($connection->connect());
            
            // check username not reserved
            if($user_database->is_exists_username($username)){
                $output = [
                    ["error"=> true, "message"=>"username already exists"]
                ]; 
            }else{
                // registration 
                $message = $user_database->add_user($arr);
                
                // is regestration seccussful 
                if(strcmp("done", $message) == 0){//successful
                    $output = [
                        ["error"=> false, "message"=>null]
                    ];
                }else{ // falied
                    $output = [
                        ["error"=> true, "message"=>"regestration failed"]
                    ];
                }
            }

            // disconnect database
            $connection->disconnect();
        }
    }

    $response->getBody()->write(json_encode($output));

    return $response;
});

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

function validate_data($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}