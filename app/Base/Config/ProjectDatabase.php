<?php

namespace Base\Config;

class ProjectDatabase{

    private $project_table        = "project";
    private $project_member_table = "project_member";
    private $project_phase_table  = "project_phase";

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

    public function get_all_projects(){
        $sql = "SELECT * FROM {$this->project_table}";
        $result = $this -> connection->query($sql);
        if($result->num_rows > 0){
            $arr = array();
            while ($row = $result->fetch_assoc()) {
                $arr[] = array(
                    'id'            => $row['id'],
                    'p_name'        => $row['p_name'],
                    'p_description' => $row['p_description'],
                    'p_start_date'  => $row['p_start_date'],
                    'p_finish_date' => $row['p_finish_date'],
                    'p_budget'      => $row['p_budget'],
                    'p_fouded'      => $row['p_founded']
                );
            }
            return $arr;
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

    public function is_in_project_member($information){
        $sql = "SELECT * FROM {$this->project_member_table} WHERE user_id={$information['user_id']} AND project_id={$information['project_id']}";
        
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            return true;
        }
        return false;
    }

    public function add_project_phase($information){
        $sql = "INSERT INTO {$this->project_phase_table} (project_id, start_date, finish_date, description, budget) VALUES 
            ({$information['project_id']}, '{$information['start_date']}', '{$information['finish_date']}', '{$information['description']}',
            {$information['budget']})";

        if($this->connection->query($sql)){
            return "done";
        }
        return null;
    }

    public function get_phases($information){
        $sql = "SELECT * FROM {$this->project_phase_table} WHERE project_id={$information['project_id']}";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
           $arr = array();
            while($row = $result->fetch_assoc()){
                $arr[] = array(
                    "id"         => $row['id'],
                    'project_id' => $row['project_id'],
                    "start_date" => $row['start_date'],
                    'finish_date'=> $row['finish_date'],
                    'budget'     => $row['budget']
                );
           }
           return $arr; 
        }
        return null;
    }

}