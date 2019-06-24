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

// add new user
$app->post('/register', function(Request $request, Response $response, array $args){
    //get data from HTTP POST
    $data = $request->getParsedBody();
    
    // check null pointer
    $name_family = isset($data['name_family']) ? validate_data($data['name_family']) : '';
    $email       = isset($data['email']) ? validate_data($data['email']) : '';
    $username    = isset($data['username']) ? validate_data($data['username']) : '';
    $password    = isset($data['password']) ? validate_data($data['password']) : '';
    
    $output = array();
    
    if(empty($name_family)){
        $output = [
            ["error"=> true , "message"=>"name_family parameter isn't set"]
        ];
    } else if(empty($email)){
        $output = [
            ["error"=> true, "message"=>"email parameter isn't set"]
        ];
    } else if(empty($username)){
        $output = [
            ["error"=> true, "message"=>"username parameter isn't set"]
        ];
    } else if(empty($email)){
        $output = [
            ["error"=> true, "message"=>"password parameter isn't set"]
        ];
    } else {
        
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

// get user information from database
$app->get('/user/information', function(Request $request, Response $response, array $args){
    $data = $request->getHeaders();
    // check null pointer
    $api_key = isset($data["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($data["HTTP_AUTHENTICATION_INFO"][0]) : '';

    $output = array();
    if(empty($api_key)){
        $output = [
            ["error"=>true, "message" => "api_key is not set"]
        ];
    }else{
        
        $connection    = new Database();
        $user_database = new UserDatabase($connection->connect());
        $result = $user_database->get_user($api_key);
        $output = [
            ["error"=>false, "message" => null],
            [
                'name_family'  => $result['name_family'],
                'email'        => $result['email'],
                'mobile_number'=> $result['mobile_number'],
                'gender'       => $result['gender'],
                'score'        => $result['score'],
                'account_card' => $result['account_card'],
                'username'     => $result['username'],
                'birthday_date'=> $result['birthday_date']
            ]
        ];
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});

// update user information 
$app->put('/user/information', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();
    // check null pointer
    $api_key        = isset($header["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($header["HTTP_AUTHENTICATION_INFO"][0]) : '';
    $name_family    = isset($data['name_family']) ? validate_data($data['name_family']) : '';
    $mobile_number  = isset($data['mobile_number']) ? validate_data($data['mobile_number']) : '';
    $gender         = isset($data['gender']) ? $data['gender'] : 0;
    $account_number = isset($data['account_number']) ? validate_data($data['account_number']) : '';
    $birthday_date  = isset($data['birthday_date']) ? date('Y-m-d', strtotime($data['birthday_date'])): '0000-00-00';
    $email          = isset($data['email']) ? validate_data($data['email']) : '';
    
    $output = array();
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
                ["error"=>false, "message" => "update done"]
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

// login user ------ Return: api_key
$app->post('/login', function(Request $request, Response $response, array $args){
    $data = $request->getParsedBody();
    // check null pointer
    $username = isset($data['username']) ? validate_data($data['username']): '';
    $password = isset($data['password']) ? validate_data($data['password']): '';
    $output = array();
    if(empty($username) || empty($password)){
        $output = [
            ["error"=> true, "message"=>"username or password isn't set"]
        ];
    }else if(!preg_match('/^[a-zA-Z0-9]{5,}$/', $username)) { // for english chars + numbers only
        // valid username, alphanumeric & longer than or equals 5 chars
        $output = [
            ["error"=> true, "message"=>"username isn't valid"]
        ];  
    }else if(!preg_match('/^[a-zA-Z0-9]{5,}$/', $password)) { // for english chars + numbers only
        // valid password, alphanumeric & longer than or equals 5 chars
        $output = [
            ["error"=> true, "message"=>"password isn't valid"]
        ];  
    }else{
        $arr = [
            "username" => $username,
            "password" => $password
        ];
    
        // connect to database
        $database   = new Database();
        $connection = $database->connect();
        // set connection to userDatabase class
        $user_database = new UserDatabase($connection);
        // get api_key
        $api_key = $user_database -> get_user_api_key($arr);
        // check username and password been correct or not
        if(empty($api_key)){
            $output = [
                ["error"=> true, "message"=>"username or password isn't correct"]
            ];  
        }else{
            $output = [
                ["error"   => false, "message"=>null],
                ["api_key" => $api_key]
            ];  
        }
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