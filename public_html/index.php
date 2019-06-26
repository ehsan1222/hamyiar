<?php
ini_set('display_errors', 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Base\Config\Database;
use Base\Config\UserDatabase;
use Base\Config\CompanyDatabase;
use Base\Middleware\UserMiddleware;
use Base\Config\ProjectDatabase;
use Base\Config\InvestorDatabase;

require_once __DIR__.'/../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c   = new \Slim\Container($configuration);
$app = new \Slim\App($c);

//-------------------------------------------------------------------------
//---------------------------------- User ---------------------------------
//-------------------------------------------------------------------------

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
            if(!empty($user_database->is_exists_username($username))){
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
        if(empty($result)){
            $output = [
                ["error"=>true, "message" => "api_key is not set"]
            ];
        }else{
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
        $connection->disconnect();
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
    $account_card   = isset($data['account_card']) ? validate_data($data['account_card']) : '';
    $birthday_date  = isset($data['birthday_date']) ? date(date('Y-m-d', strtotime($data['birthday_date']))): '';
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
            'account_card'   => $account_card,
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
        $database->disconnect();
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});

//-------------------------------------------------------------------------
//---------------------------------- Company ------------------------------
//-------------------------------------------------------------------------
// add new company
$app->post('/companies/add', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();

    $api_key        = isset($header["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($header["HTTP_AUTHENTICATION_INFO"][0]) : '';
    $c_name         = isset($data['c_name']) ? validate_data($data['c_name']) : ''; 
    $c_address      = isset($data['c_address']) ? validate_data($data['c_address']) : ''; 
    $c_email        = isset($data['c_email']) ? validate_data($data['c_email']) : ''; 
    $c_founded_date = isset($data['c_founded_date']) ? validate_data(date('Y-m-d', strtotime($data['c_founded_date']))): ''; 
    $c_description  = isset($data['c_description']) ? validate_data($data['c_description']) : ''; 
    $c_tel          = isset($data['c_tel']) ? validate_data($data['c_tel']) : '';
    $position       = isset($data['position']) ? validate_data($data['position']) : '';
    
    if (empty($api_key)) {
        $output = [
            ["error"=> true, "message"=>"api_key isn't set"]
        ];
    } else if(empty($position)){
        $output = [
            ["error"=> true, "message"=>"position isn't set"]
        ];
    }else if(empty($c_name)){
        $output = [
            ["error"=> true, "message"=>"c_name isn't set"]
        ];
    } else if(empty($c_address)){
        $output = [
            ["error"=> true, "message"=>"c_address isn't set"]
        ];
    } else if(empty($c_email)){
        $output = [
            ["error"=> true, "message"=>"c_email isn't set"]
        ];
    } else if(empty($c_founded_date)){
        $output = [
            ["error"=> true, "message"=>"c_founded_date isn't set"]
        ];
    } else if(empty($c_description)){
        $output = [
            ["error"=> true, "message"=>"c_description isn't set"]
        ];
    } else if(empty($c_tel)){
        $output = [
            ["error"=> true, "message"=>"c_tel isn't set"]
        ];
    } else if( (strlen($c_tel) != 11) || (!preg_match('/^[0-9]*$/', $c_tel) ) ){
        $output = [
            ["error"=> true, "message"=>"format c_tel isn't correct"]
        ];
    } else if (!filter_var($c_email, FILTER_VALIDATE_EMAIL)) {
        $output = [
            ["error"=> true, "message"=>"invalid c_email format"]
        ]; 
    } else{
        $database   = new Database();
        $connection = $database->connect();

        $company_database = new CompanyDatabase($connection);
        $arr = [
            'c_name'         => $c_name,
            'c_address'      => $c_address,
            'c_email'        => $c_email,
            'c_founded_date' => $c_founded_date,
            'c_description'  => $c_description,
            'c_tel'          => $c_tel
        ];

        // get user information
        $user_database    = new UserDatabase($connection);
        $user_information = $user_database -> get_user($api_key);
        if(empty($user_information)){
            $output = [
                ["error"=> true, "message"=>"api_key isn't valid"]
            ]; 
        }else{
            $result = $company_database->add_company($arr);
            if(empty($result)){
                $output = [
                    ["error"=> true, "message"=>"company doesn't created"]
                ]; 
            }else{
                // get this company information
                $arr = [
                    'c_name'  => $c_name,
                    'c_email' => $c_email
                ];
                $company_information = $company_database->get_company($arr);
                if(empty($company_information)){
                    $output = [
                        ["error"=> true, "message"=>"company doesn't exists"]
                    ];  
                } else{
                    $arr = [
                        'user_id' => $user_information['id'],
                        'company_id'    => $company_information['id'],
                        'position'=> $position
                    ];

                    $result = $company_database->add_member($arr);
                    if(empty($result)){
                        $output = [
                            ["error"=> true, "message"=>"member doesn't add"]
                        ]; 
                    }else{
                        $output = [
                            ["error"=> false, "message"=>null]
                        ]; 
                    }
                }
            }
        }

        $database->disconnect();
    }

    $response -> getBody() -> write(json_encode($output));
    return $response;
});

// add new memeber to company
$app->post('/companies/add/member', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();

    $api_key    = isset($header["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($header["HTTP_AUTHENTICATION_INFO"][0]) : '';
    $company_id = isset($data['company_id']) ? validate_data($data['company_id']) : "";
    $username   = isset($data['username']) ? validate_data($data['username']) : "";
    $position   = isset($data['position']) ? validate_data($data['position']) : "";
    
    $output = array();
    if (empty($api_key)) {
        $output = [
            ["error"=> true, "message"=>"api_key isn't set"]
        ];
    } else if(empty($company_id)){
        $output = [
            ["error"=> true, "message"=>"company_id isn't set"]
        ];
    } else if(empty($username)){
        $output = [
            ["error"=> true, "message"=>"username isn't set"]
        ];
    } else if(empty($position)){
        $output = [
            ["error"=> true, "message"=>"position isn't set"]
        ];
    } else{
        $database   = new Database();
        $connection = $database->connect();

        // check api_key is valid
        $user_database    = new UserDatabase($connection);
        $company_database = new CompanyDatabase($connection);
        $user_information = $user_database->get_user($api_key);
        if(empty($user_information)){
            $output = [
                ["error"=> true, "message"=>"api_key isn't valid"]
            ];
        }else{
            // check this user can add member to specific company 
            $arr=[
                'user_id'    => $user_information['id'],
                'company_id' => $company_id
            ];
            $result = $company_database -> is_member_in_company($arr);
            if(!$result){
                $output = [
                    ["error"=> true, "message"=>"api_key can't add member"]
                ];
            }else{
                // get user id for add to member table
                $result = $user_database->is_exists_username($username);
                if(empty($result)){
                    $output = [
                        ["error"=> true, "message"=>"username not found"]
                    ];
                }else{
                    //check if member already be in this row
                    $arr=[
                        'user_id'    => $result['id'],
                        'company_id' => $company_id
                    ];
                    $result = $company_database -> is_member_in_company($arr);
                    if(empty($result)){
                        $arr=[
                            "user_id"    => $result['id'],
                            'company_id' => $company_id,
                            "position"   => $position
                        ];
                        $result = $company_database -> add_member($arr);
                        if(empty($result)){
                            $output = [
                                ["error"=> true, "message"=>"add member failed"]
                            ];
                        }else{
                            $output = [
                                ["error"=> false, "message"=>"add member done"]
                            ];
                        }
                    }else{
                        $output = [
                            ["error"=> true, "message"=>"username alredy been in this company"]
                        ];
                    }
                }

            }
        
        }
        $database->disconnect();
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});


// get all companies data
$app->get('/companies', function(Request $request, Response $response, array $args){
    $database   = new Database();
    $connection = $database -> connect();
    
    $company_database = new CompanyDatabase($connection);
    $result = $company_database->get_all_companies();
    $output = array();
    if(empty($result)){
        $output = [
            ["error"=> true, "message"=>"no company founded"]
        ];
    }else{
        $output [] = ["error"=> false, "message"=>null];
        $output [] = $result;
    }
    $database->disconnect();

    $response->getBody()->write(json_encode($output));
    return $response;
});

//-------------------------------------------------------------------------
//---------------------------------- Project ------------------------------
//-------------------------------------------------------------------------

// return all projects
$app->get('/projects', function(Request $request, Response $response, array $args){
    $database   = new Database();
    $connection = $database->connect();
    $project_database = new ProjectDatabase($connection); 
    $project_informations = $project_database->get_all_projects();
    $output = array();
    if(empty($project_informations)){
        $output = [
            ["error"=> true, "message"=>"nothing to show"]
        ];
    }else{
        $output[] = ["error"=>false, "message"=>null];
        $output[] = $project_informations;
    }
    $database->disconnect();
    $response->getBody()->write(json_encode($output));
    return $response;
});

// add new project
$app->post('/projects', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();

    $api_key    = isset($header["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($header["HTTP_AUTHENTICATION_INFO"][0]) : '';
    $p_name = isset($data['p_name']) ? validate_data($data['p_name']) : "";
    $p_description = isset($data['p_description']) ? validate_data($data['p_description']) : "";
    $p_start_date = isset($data['p_start_date']) ? validate_data( date('Y-m-d', strtotime($data['p_start_date']))): ''; 
    $p_finish_date = isset($data['p_finish_date']) ? validate_data(date('Y-m-d', strtotime($data['p_finish_date']))): ''; 
    $p_budget = isset($data['p_budget']) ? validate_data($data['p_budget']) : "";
    $position = isset($data['position']) ? validate_data($data['position']) : "";
    
    $output = array();
    if(empty($api_key)){
        $output = [
            ["error"=> true, "message"=>"api_key isn't set"]
        ];
    }else if(empty($p_name)){
        $output = [
            ["error"=> true, "message"=>"p_name isn't set"]
        ];
    }else if(empty($p_description)){   
        $output = [
            ["error"=> true, "message"=>"p_description isn't set"]
        ];
    }else if(empty($p_start_date)){
        $output = [
            ["error"=> true, "message"=>"p_start_date isn't set"]
        ];
    }else if(empty($p_finish_date)){
        $output = [
            ["error"=> true, "message"=>"p_finish_date isn't set"]
        ];
    }else if(empty($p_budget)){
        $output = [
            ["error"=> true, "message"=>"p_budget isn't set"]
        ];
    }else if(empty($position)){
        $output = [
            ["error"=> true, "message"=>"position isn't set"]
        ];
    }else{
        $database   = new Database();
        $connection = $database->connect();

        $user_database    = new UserDatabase($connection);
        $project_database = new ProjectDatabase($connection);

        $user_information = $user_database->get_user($api_key);
        if(empty($user_information)){
            $output = [
                ["error"=> true, "message"=>"api_key isn't valid"]
            ];
        }else{
            // check this project name didn't reserve
            $project_information = $project_database->get_project($p_name);
            if(!empty($project_information)){
                $output = [
                    ["error"=> true, "message"=>"this project name already exists"]
                ];
            }else{
                $arr = [
                    'p_name'        => $p_name,
                    'p_description' => $p_description,
                    'p_start_date'  => $p_start_date,
                    'p_finish_date' => $p_finish_date,
                    'p_budget'      => $p_budget
                ];
                // add project in project table
                $result = $project_database->add_project($arr);
                if(empty($result)){
                    $output = [
                        ["error"=> true, "message"=>"project doesn't add"]
                    ];  
                }else{
                    $project_information = $project_database->get_project($p_name);
                    if(empty($project_information)){
                        $output = [
                            ["error"=> true, "message"=>"project doesn't exist"]
                        ];
                    }else{
                        $arr = [
                            'user_id'    => $user_information['id'],
                            'project_id' => $project_information['id'],
                            'position'   => $position
                        ];
                        $result = $project_database->add_project_member($arr);
                        if(empty($result)){
                            $output = [
                                ["error"=> true, "message"=>"member doesn't add"]
                            ];
                        }else{
                            $output = [
                                ["error"=> false, "message"=>null]
                            ];
                        }
                    }
                }
            }
        }

        $database->disconnect();
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});

// add new phase
$app->post('/projects/phases', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();

    $api_key     = isset($header["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($header["HTTP_AUTHENTICATION_INFO"][0]) : '';
    $project_id  = isset($data['project_id']) ? validate_data($data['project_id']) : "";
    $start_date  = isset($data['start_date']) ? validate_data(date('Y-m-d', strtotime($data['start_date']))) : "";
    $finish_date = isset($data['finish_date']) ? validate_data(date('Y-m-d', strtotime($data['finish_date']))) : "";
    $description = isset($data['description']) ? validate_data($data['description']) : "";
    $budget      = isset($data['budget']) ? validate_data($data['budget']) : "";
    $output = array();
    if(empty($api_key)){
        $output = [
            ["error"=> false, "message"=>"api_key isn't set"]
        ];
    } else if(empty($project_id)){
        $output = [
            ["error"=> true, "message"=>"project_id isn't set"]
        ];
    } else if(empty($start_date)){
        $output = [
            ["error"=> true, "message"=>"start_date isn't set"]
        ];
    } else if(empty($finish_date)){
        $output = [
            ["error"=> true, "message"=>"finish_date isn't set"]
        ];
    } else if(empty($description)){
        $output = [
            ["error"=> true, "message"=>"description isn't set"]
        ];
    } else if(empty($budget)){
        $output = [
            ["error"=> true, "message"=>"budget isn't set"]
        ];
    } else{
        $database   = new Database();
        $connection = $database->connect();
        
        $user_database    = new UserDatabase($connection);
        $project_database = new ProjectDatabase($connection);
        
        $user_information = $user_database->get_user($api_key);
        if(empty($user_information)){
            $output = [
                ["error"=> true, "message"=>"api_key isn't valid"]
            ];
        }else{
            // check this user is member of project
            $arr = [
                'user_id'    => $user_information['id'],
                'project_id' => $project_id 
            ];
            if($project_database -> is_in_project_member($arr)){
                $arr = [
                    'project_id' => $project_id,
                    'start_date' => $start_date,
                    'finish_date'=> $finish_date,
                    'description'=> $description,
                    'budget'     => $budget
                ];
                $result = $project_database -> add_project_phase($arr);
                if(empty($result)){
                    $output = [
                        ["error"=> true, "message"=>"project phase doesn't add"]
                    ];
                }else{
                    $output = [
                        ["error"=> false, "message"=>null]
                    ];
                }
            }else{
                $output = [
                    ["error"=> true, "message"=>"api_key doesn't permissed to add phase in this project"]
                ];
            }
        }
        $database -> disconnect();
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});

$app->get('/projects/phases', function(Request $request, Response $response, array $args){
    $data = $request->getQueryParams();
    $project_id = isset($data['project_id']) ? $data['project_id'] : "";
    
    $output = array();
    if(empty($project_id)){
        $output = [
            ["error"=> true, "message"=>"project_id isn't set"]
        ];
    }else{
        $database = new Database();
        $connection = $database->connect();
    
        $arr = [
            'project_id' => $project_id
        ];
    
        $project_database = new ProjectDatabase($connection);
        $project_phase_information = $project_database->get_phases($arr);
    
        if(empty($project_phase_information)){
            $output = [
                ["error"=> true, "message"=>"project_id isn't valid or project have any phase"]
            ];
        }else{
            $output [] = array("error"=> false, "message"=>null);
            $output [] = $project_phase_information;
        }
    
        $database->disconnect();
    }


    $response->getBody()->write(json_encode($output));
    return $response;
});


//-------------------------------------------------------------------------
//---------------------------------- Investor -----------------------------
//-------------------------------------------------------------------------

$app->post('/investor', function(Request $request, Response $response, array $args){
    $header = $request -> getHeaders();
    $data   = $request -> getParsedBody();

    $MONTH_LATER = '3';

    $api_key    = isset($header["HTTP_AUTHENTICATION_INFO"][0]) ? validate_data($header["HTTP_AUTHENTICATION_INFO"][0]) : '';
    $amount = isset($data["amount"]) ? validate_data($data['amount']) : "";
    $p_name = isset($data["p_name"]) ? validate_data($data['p_name']) : "";
    $company_id = isset($data["company_id"]) ? validate_data($data['company_id']) : "";
    
    // time return money
    $return_date = date('Y-m-d', strtotime("+{$MONTH_LATER} months"));
    
    $output = array();
    if(empty($api_key)){
        $output = [
            ["error"=> true, "message"=>"api_key isn't set"]
        ];
    } else if(empty($amount)){
        $output = [
            ["error"=> true, "message"=>"amount isn't set"]
        ];
    } else if(empty($p_name)){
        $output = [
            ["error"=> true, "message"=>"p_name isn't set"]
        ];
    } else {
        $database   = new Database();
        $connection = $database -> connect();
        
        $user_database    = new UserDatabase($connection);
        $user_information = $user_database->get_user($api_key); 

        if(empty($user_information)){
            $output = [
                ["error"=> true, "message"=>"api_key isn't valid"]
            ];
        }else{
            $project_database = new ProjectDatabase($connection);
            $project_information = $project_database-> get_project($p_name);
            if(empty($project_information)){
                $output = [
                    ["error"=> true, "message"=>"p_name isn't valid"]
                ];
            }else{
                if(empty($company_id)){
                    // this is user investor
                    $bank_receipt = pay_process();
                    if(empty($bank_receipt)){
                        $output = [
                            ["error"=> true, "message"=>"pay process unfinished"]
                        ];  
                    }else{
                        $arr = [
                            'user_id'      => $user_information['id'],
                            'project_id'   => $project_information['id'],
                            'amount'       => $amount,
                            'return_date'  => $return_date,
                            'bank_receipt' => $bank_receipt 
                        ];
                        
                        $investor_database = new InvestorDatabase($connection);
                        $result = $investor_database->add_user_investor($arr);
                        if(empty($result)){
                            $output = [
                                ["error"=> true, "message"=>"pay process didnt save in database"]
                            ]; 
                        }else{
                            $output = [
                                ["error"=> false, "message"=>null]
                            ]; 
                        }
                    }
                    
                }else{
                    // this is company investor
                    $arr=[
                        'user_id'    => $user_information['id'],
                        'company_id' => $company_id
                    ];
                    $company_database = new CompanyDatabase($connection);
                    if($company_database->is_member_in_company($arr)){
                        $bank_receipt = pay_process();
                        if(empty($bank_receipt)){
                            $output = [
                                ["error"=> true, "message"=>"pay process unfinished"]
                            ];  
                        }else{
                            // pay process done successfully
                            $arr = [
                                'company_id'  => $company_id,
                                'project_id'  => $project_information['id'],
                                'amount'      => $amount,
                                'return_date' => $return_date,
                                'bank_receipt'=> $bank_receipt
                            ];
                            $investor_database = new InvestorDatabase($connection);
                            $result = $investor_database->add_company_investor($arr);
                            if(empty($result)){
                                $output = [
                                    ["error"=> true, "message"=>"pay process didnt save in database"]
                                ]; 
                            }else{
                                $output = [
                                    ["error"=> false, "message"=>null]
                                ]; 
                            }
                        }
                    }else{
                        $output = [
                            ["error"=> true, "message"=>"api_key didn't permission to investicate in this company"]
                        ];  
                    }
    
                }
            }
        }

        $database->disconnect();
    }
    $response->getBody()->write(json_encode($output));
    return $response;
});

function pay_process(){

    return "456487987852";
}

$app->run();

function validate_data($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}