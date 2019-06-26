<?php
namespace Base\Config;

class InvestorDatabase{

    private $connection;

    private $user_investor_table    = "user_investor";
    private $company_investor_table = "company_investor";

    public function __construct($connection){
        $this->connection = $connection;
    }


    public function add_user_investor($information){
        $sql = "INSERT INTO {$this->user_investor_table} (user_id, project_id, amount, return_date, bank_receipt) VALUES 
                ({$information['user_id']}, {$information['project_id']}, {$information['amount']}, '{$information['return_date']}', '{$information['bank_receipt']}')";
        if($this->connection->query($sql)){
            return "done";
        }
        return null;
    }
    				

    public function add_company_investor($information){
        $sql = "INSERT INTO {$this->company_investor_table} (company_id, project_id, amount, return_date, bank_receipt) VALUES 
                ({$information['company_id']}, {$information['project_id']}, {$information['amount']}, '{$information['return_date']}', '{$information['bank_receipt']}')";
        if($this->connection->query($sql)){
            return "done";
        }
        return null;
    }


}