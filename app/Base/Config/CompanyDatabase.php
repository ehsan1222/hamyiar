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

    public function get_all_companies(){
        $sql = "SELECT * FROM company";
        $result = $this->connection->query($sql);
        if($result -> num_rows <=0){
            return null;
        }
        $arr = array();
        while($row = $result->fetch_assoc()){
            $tmp = [
                'id' => $row['id'],
                'c_name' => $row['c_name'],
                'c_address' => $row['c_address'],
                'c_email' => $row['c_founded_date'],
                'c_description' => $row['c_description'],
                'c_tel' => $row['c_tel']
            ];
            $arr[] = $tmp;
        }
        return $arr;
    }

    public function get_company_information($information){
        $company_id = $information['id'];
        $sql = "SELECT * FROM company WHERE id={$company_id}";
    
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        }else{
            return null;
        }
    }

    public function is_member_in_company($information){
        $user_id    = $information['user_id'];
        $company_id = $information['company_id'];
        
        $sql = "SELECT * FROM {$this->member_table} WHERE user_id={$user_id} AND company_id={$company_id}";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            return true;
        }
        return false;
    }

}