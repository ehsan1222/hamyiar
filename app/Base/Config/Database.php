<?php
namespace Base\Config;

class Database{
    private $host = 'localhost';
    private $database_name = "hamyiar";
    private $username = "ehsan1222";
    private $password = "omid1376";
    private $connection = null;

    public function connect(){

        try{
            $this->connection = new \PDO("mysql:host={$this->host};dbname={$this->database_name}", $this->username, $this->password);
            $this->connection -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }catch(\PDOException $exception){
            echo $exception->getMessage();
        }
        return $this->connection;
    }
}