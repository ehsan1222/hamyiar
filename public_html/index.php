<?php
ini_set('display_errors', 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Base\Config\Database;
use Base\Config\UserDatabase;
use Base\Middleware\UserMiddleware;

require_once __DIR__.'/../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c   = new \Slim\Container($configuration);
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


$app->get('/user/information', function(Request $request, Response $response, array $args){
    $data = $request->getHeaders();
    
    $output = array();
    if(empty($data["HTTP_AUTHENTICATION_INFO"])){
        $output = [
            ["error"=>true, "message" => "api_key is not set"]
        ];
    }else{
        $api_key = validate_data($data["HTTP_AUTHENTICATION_INFO"][0]);
        $connection    = new Database();
        $user_database = new UserDatabase($connection->connect());
        $result = $user_database->get_user($api_key);
        $output = [
            ["error"=>false, "message" => null],
            [
                'name_family'  =>$result['name_family'],
                'email'        =>$result['email'],
                'mobile_number'=>$result['mobile_number'],
                'gender'       =>$result['gender'],
                'score'        =>$result['score'],
                'account_card' =>$result['account_card'],
                'username'     =>$result['username'],
                'birthday_date'=>$result['birthday_date']
            ]
        ];
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});


$app->put('/user/information', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();
    $output = array();
    
    $api_key        = validate_data($header["HTTP_AUTHENTICATION_INFO"][0]);
    $name_family    = validate_data($data['name_family']);
    $mobile_number  = validate_data($data['mobile_number']);
    $gender         = $data['gender'];
    $account_number = validate_data($data['account_number']);
    $birthday_date  = date('Y-m-d', strtotime($data['birthday_date']));
    $email          = validate_data($data['email']);

    if(empty($api_key)){
        $output = [
            ["error"=>true, "message" => "api_key is not set"]
        ];
    } else if(empty($name_family)){
        $output = [
            ["error"=>true, "message" => "name_family is not set"]
        ];
    } else if(empty($email)){
        $output = [
            ["error"=>true, "message" => "email is not set"]
        ];
    } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $output = [
            ["error"=>true, "message" => "invalid email format"]
        ];
    }else{
        // connect to database
        $database   = new Database();
        $connection = $database->connect();
        
        // set connection to user database
        $user_database = new UserDatabase($connection);

        // user information arr
        $arr = [
            'api_key'        => $api_key,
            'name_family'    => $name_family,
            'mobile_number'  => $mobile_number,
            'gender'         => $gender,
            'account_number' => $account_number,
            'birthday_date'  => $birthday_date,
            'email'          => $email
        ];

        $result = $user_database -> update_user($arr);    
        if(strcmp($result, "done") == 0){
            $output = [
                ["error"=>true, "message" => "update done"]
            ];
        }else{
            $output = [
                ["error"=>true, "message" => "update failed"]
            ];
        }
        $database->disconnect();
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});



$app->run();

function validate_data($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}