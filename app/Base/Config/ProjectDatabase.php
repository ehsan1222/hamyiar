<?php

namespace Base\Config;

class ProjectDatabase{

    private $project_table        = "project";
    private $project_member_table = "project_member";
    private $connection;
    
    public function __construct($connection){
        $this->connection = $connection;
    }

    public function add_project($project_information){
        $founded = '0';
        $sql = "INSERT INTO {$this->project_table} (p_name, p_description, p_start_date, p_finish_date, p_budget, p_founded) VALUES  
        ('{$project_information['p_name']}', '{$project_information['p_description']}', '{$project_information['p_start_date']}',
        '{$project_information['p_finish_date']}', {$project_information['p_budget']}, {$founded})";

        if($this->connection->query($sql)){
            return "done";
        }
        return null;
    }

    public function get_project($p_name){
        $sql    = "SELECT * FROM project WHERE p_name='{$p_name}'";
        $result = $this->connection->query($sql);
        if($result->num_rows>0){
            return $result->fetch_assoc(); 
        }
        return null;
    }

    public function add_project_member($information){
        $sql = "INSERT INTO {$this->project_member_table} (user_id, project_id, position) VALUES 
        ({$information['user_id']}, {$information['project_id']}, '{$information['position']}')";

        if($this->connection->query($sql)){
            return "done";
        }
        return null;
    }

}