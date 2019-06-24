<?php
namespace Base\Config;

class Database{
    private $host = 'localhost';
    private $database_name = "hamyiar";
    private $username = "root";
    private $password = "";

    private $connection = null;

    // connecto to database
    public function connect(){
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database_name);
        $this->connection->set_charset("utf-8");

        if ($this->connection->connect_error) {
            echo "server Error: connect() function";
        }
        
        return $this->connection;
    }

    // close database connection
    public function disconnect(){
        if($this->connection != null) $this->connection->close();
    }

    
}