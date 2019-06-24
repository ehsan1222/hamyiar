<?php
namespace Base\Config;

class UserDatabase{

    private $connection ;

    // users table name
    private $table_name = 'users';

    // set connection value
    public function __construct($connection){
        $this->connection = $connection;
    }

    public function add_user(array $user_information){
        // get basic informations 
        $name_family = $user_information['name_family'];
        $email       = $user_information['email'];
        $username    = $user_information['username'];
        $salt        = uniqid();
        $password    = $this->create_secure_password( $user_information['password'], $salt);
        $api_key     = $this->create_hash_value(uniqid());
        $score       = 0;
        
        // add user in users database
        $query = "INSERT INTO {$this->table_name} (name_family, email, username, salt, password, api_key, score) VALUES
                    ('{$name_family}', '{$email}', '{$username}', '{$salt}', '{$password}', '{$api_key}', {$score})";

        // execute query
        if ($this->connection->query($query)) {
            return "done";
        }else{
            return "error";
        }
    }
    
    public function get_user($api_key){
        $sql = "SELECT * FROM users Where api_key='$api_key'";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        }else{
            return "api_key isn't correct";
        } 
    }

    // check username is already exists or not
    public function is_exists_username($username){
        $sql = "SELECT * FROM {$this->table_name} WHERE username='$username'";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            return "username already exists";
        }else{
            return null;
        }
    }


    // produce secure password
    public function create_secure_password($password, $salt){
        // mix password and salt
        $pass_plus_salt = $password."".$salt;
        
        $hashed_password = $this->create_hash_value($pass_plus_salt);
        return $hashed_password;
    }


    public function create_hash_value($value){
        return hash('sha256', $value);
    }


    

}