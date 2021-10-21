<?php

class Users
{
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_name;
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Users";
        $this->class_name_lower = "users_class";
        $this->table_name = "users";
    }

    public function register ($name, $email, $password, $role, $status = 'U')
    {
        $q = "INSERT INTO `{$this->table_name}` (`user_name`, `user_email`, `user_password`, `user_role`, `user_status`, `user_created`) VALUE (:n, :e, :p, :r, :s, :dt)";
        $s = $this->db->prepare($q);
        $s->bindParam(":n", $name);
        $s->bindParam(":e", $email);
        $s->bindParam(":p", $password);
        $s->bindParam(":r", $role);
        $s->bindParam(":s", $status);
        $dt = current_date();
        $s->bindParam(":dt", $dt);

        if (!$s->execute()) {
            $failure = $this->class_name.'.register - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        
        return ['status' => true, 'user_id' => $this->db->lastInsertId()];
    }

    public function get_user_by ($col, $val)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `$col` = :v";
        $s = $this->db->prepare($q);
        $s->bindParam(":v", $val);

        if (!$s->execute()) {
            $failure = $this->class_name.'.get_user_by - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }

        if ($s->rowCount() === 0) {
            return ['status' => false, 'type' => 'empty'];
        }
        return ['status' => true, 'data' => $s->fetch()];
    }

    public function login ($email, $password)
    {
        $user = $this->get_user_by('user_email', $email);
        if (!$user['status']) {
            return ['status' => false, 'data' => 'Provided email is not accociated with any account.'];
        }
        $user = $user['data'];
        
        if ($user['user_status'] === 'U') {
            return ['status' => false, 'data' => 'Account is not verified.'];
        }
        if ($user['user_status'] === 'B') {
            return ['status' => false, 'data' => 'Account is banned.'];
        }

        if (!password_verify($password, $user['user_password'])) {
            return ['status' => false, 'data' => 'Provided password is incorrect.'];
        }

        $this->set_session($user['user_id']);

        return ['status' => true, 'user' => $user];
    }

    public function check_user_status ($status)
    {
        if ($status === 'U') {
            return ['status' => false, 'data' => 'Account is not verified.'];
        }
        if ($status === 'B') {
            return ['status' => false, 'data' => 'Account is banned.'];
        }

        return ['status' => true];
    }

    public function set_session ($user_id)
    {
        $_SESSION['logged'] = true;
        $_SESSION['logged_user'] = $user_id;
    }

    public function mark_verified ($user_id)
    {
        $q = "UPDATE `users` SET `user_status` = 'A' WHERE `user_id` = :u";
        $s = $this->db->prepare($q);
        $s->bindParam(":u", $user_id);

        if (!$s->execute()) {
            $failure = $this->class_name.'.mark_verified - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        
        return ['status' => true];
    }

    public function logout ()
    {
        if (isset($_SESSION['logged'])) {
            unset($_SESSION['logged']);
        }
        if (isset($_SESSION['logged_user'])) {
            unset($_SESSION['logged_user']);
        }
        return true;
    }

    public function get_logged_user ()
    {
        $user = $this->get_user_by('user_id', $_SESSION['logged_user']);
        if (!$user['status']) {
            return ['status' => false, 'data' => 'Unable to find user details'];
        }

        $user = $user['data'];

        $status = $this->check_user_status($user['user_status']); 
        if (!$status['status']) {
            return $status;
        }

        return ['status' => true, 'data' => $user];
    }

    public function update ($changes, $user_id)
    {
        $p = "";
        $data = [];
        foreach ($changes as $c => $v) {
            if (!empty($p)) {
                $p .= ", ";
            }
            $p .= "`$c` = :$c";
            $data[":$c"] = $v;
        }

        $data[":user_id"] = $user_id;

        $q = "UPDATE `users` SET $p WHERE `user_id` = :user_id";

        $s = $this->db->prepare($q);

        if (!$s->execute($data)) {
            $failure = $this->class_name.'.update - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        
        return ['status' => true];

    }

}
