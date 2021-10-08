<?php

class Codes
{
    
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_name;
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Codes";
        $this->class_name_lower = "codes_class";
        $this->table_name = "codes";
    }
    
    public function record ($user_id, $code, $type) {
        $q = "INSERT INTO `{$this->table_name}` (`code_unique`, `code_user_id`, `code_type`, `code_created`) VALUE (:c, :u, :t, :dt)";
        
        $s = $this->db->prepare($q);
        $s->bindParam(":c", $code);
        $s->bindParam(":u", $user_id);
        $s->bindParam(":t", $type);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        if (!$s->execute()) {
            $failure = $this->class_name.'.record - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true];
    }

    public function get_code_by_user_id ($code, $user_id, $type)
    {
        $q = "SELECT * FROM `codes` WHERE `code_user_id` = :u AND `code_unique` = :c AND `code_type` = :t";

        $s = $this->db->prepare($q);
        $s->bindParam(":c", $code);
        $s->bindParam(":u", $user_id);
        $s->bindParam(":t", $type);

        if (!$s->execute()) {
            $failure = $this->class_name.'.get_code_by_user_id - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }
        return ['status' => true, 'data' => $s->fetch()];
    }

    public function mark_code_used ($code_id)
    {
        $q = "UPDATE `codes` SET `code_used` = 'Y', `code_used_on` = :dt WHERE `code_id` = :c";
        
        $s = $this->db->prepare($q);
        $s->bindParam(":c", $code_id);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        
        if (!$s->execute()) {
            $failure = $this->class_name.'.mark_code_used - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        return ['status' => true];
    }

}
