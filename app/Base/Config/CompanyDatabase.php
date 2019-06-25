<?php
namespace Base\Config;


class CompanyDatabase{

    private $connection;
    //table names
    private $company_table = "company";
    private $member_table  = "member";

    public function __construct($connection){
        $this->connection = $connection;
    }

    // add new company
    public function add_company($information){
        $sql = "INSERT INTO {$this->company_table} (c_name, c_address, c_email, c_founded_date, c_description, c_tel) VALUES 
            ('{$information['c_name']}', '{$information['c_address']}', '{$information['c_email']}','{$information['c_founded_date']}', '{$information['c_description']}',
            '{$information['c_tel']}')";
        
        if (!$this->connection->query($sql)) {
            return null;
        }
        return "done";
    }

    // get company id
    public function get_company($information){
        $sql    = "SELECT * FROM {$this->company_table} WHERE c_name='{$information['c_name']}' AND c_email='{$information['c_email']}'";
        $result = $this->connection->query($sql);
        
        if($result -> num_rows > 0){
            return $result->fetch_assoc();
        }else{
            return null;
        }
    }

    public function add_member($information){
        $sql = "INSERT INTO {$this->member_table} (user_id, company_id, position) VALUES 
        ({$information['user_id']}, {$information['company_id']}, '{$information['position']}')";

        if($this->connection->query($sql)){
            return "done";
        }
        return null;
    }

    public function remove_company($information){

    }

}