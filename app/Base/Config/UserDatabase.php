<?php
namespace Base\Config;

class UserDatabase{

    private $connection ;
    private $table_name = 'users';

    // set connection value
    function __construct($connection){
        $this->connection = $connection;
    }

    // basic 
    function add_user(array $user_information){
        // get basic informations 
        $name_family = $user_information['name_family'];
        $email       = $user_information['email'];
        $username    = $user_information['username'];
        $salt        = uniqid();
        $password    = create_secure_password($user_information['password'], $salt);
        $api_key     = create_hash_value(uniqid());
        $score       = 0;
        
        // add user SQL query
        $query = "INSERT INTO users (name_family, email, username, salt, password, api_key, score) 
                    Values(?, ?, ?, ?, ?, ?, ?)";
        

        $data=[
            'name_family' => $name_family,
            'email'       => $email, 
            'username'    => $username, 
            'salt'        => $salt, 
            'password'    => $password, 
            'api_key'     => $api_key,
            'score'       => $score
        ];


        // add user 
        $this->connection->prepare($query)->execute($data);

        return $api_key;
    }
    
    function get_user($api_key){
        $query     = "SELECT * FROM {$this->table_name}";
        $statement = $this->connection->prepare($query);
        $statement -> execute();
        return $statement; 
    }


    // produce secure password
    function create_secure_password($password, $salt){
        // mix password and salt
        $pass_plus_salt = $password+$salt;
        
        $hashed_password = create_hash_value($pass_plus_salt);
        return $hashed_password;
    }


    function create_hash_value($value){
        return hash('sha256', $value);
    }


    

}