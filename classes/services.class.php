<?php

class Services
{
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_access;
    private $table_services;

    public $services;
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Services";
        $this->class_name_lower = "services_class";
        $this->table_access = "access_tokens";
        $this->table_services = "music_services";

        $this->services = $this->get_all_services();
    }

    public function get_all_services ()
    {
        $q = "SELECT * FROM `{$this->table_services}`";
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_all_services - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        if ($s->rowCount() < 1) {
            return ['status' => false, 'type' => 'empty', 'data' => 'No services found.'];
        }
        return ['status' => true, 'data' => $s->fetchAll()];
    }

    public function get_service_by_name ($service_name)
    {
        $services = $this->services;

        if (!$services['status']) { return $services; }
        else { $services = $services['data']; }

        foreach ($services as $service) {
            if ($service['mservice_name'] == $service_name) {
                return ['status' => true, 'data' => $service];
            }
        }

        $failure = $this->class_name.'.get_service_by_name - E.02: Failure';
        $this->logs->create($this->class_name_lower, $failure, json_encode(['passed_name' => $service_name]));

        return ['status' => false, 'type' => 'empty', 'data' => 'Invalid service name.'];
    }

    public function get_access_token_of_service ($service_id, $join = false)
    {
        if ($join !== false) {
            $q = "SELECT * FROM `{$this->table_access}` JOIN `{$this->table_services}` ON `atoken_mservice_id` = `mservice_id` WHERE `atoken_mservice_id` = :i AND `atoken_expired` = 'N'";
        } else {
            $q = "SELECT * FROM `{$this->table_access}` WHERE `atoken_mservice_id` = :i AND `atoken_expired` = 'N'";
        }
        $s = $this->db->prepare($q);
        $s->bindParam(":i", $service_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_access_token_of_service - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        if ($s->rowCount() < 1) {
            return ['status' => false, 'type' => 'empty', 'data' => 'No token found.'];
        }

        return ['status' => true, 'data' => $s->fetch()];
    }

    /**
     * check if the token is expired 
     * if expired -> get new token and update into database
     */

    public function get_verified_access_token ($service_id, $session)
    {
        $condition = ['new' => false, 'old' => false];

        $_access_token = $this->get_access_token_of_service($service_id);
        
        if ($_access_token['status']) {
            $_access_token = $_access_token['data'];
            $token = $_access_token['atoken_token'];

            $remaining_time = strtotime($_access_token['atoken_expiry']) - time();
            if ($remaining_time <= 10) {
                $condition['new'] = $condition['old'] = true;
            }

        } else {
            $condition['new'] = true;
        }

        if ($condition['new']) {
            // getting new token
            $check = $this->generate_access_token($service_id, $session, $condition['old']);
            if ($check['status']) {
                $token = $check['access_token'];
            } else {
                return ['status' => false, 'type' => 'error', 'data' => 'Unable to generate token'];
            }
        }

        return ['status' => true, 'token' => $token];
    }

    public function generate_access_token ($service_id, $session, $expire_previous = true)
    {
        $session->requestCredentialsToken();
        $access_token = $session->getAccessToken();
        $access_token_expiry = date('Y-m-d H:i:s', $session->getTokenExpiration());


        $check = $this->insert_access_token($service_id, $access_token, $access_token_expiry, $expire_previous);
        if (!$check['status']) { return $check; }

        return ['status' => true, 'access_token' => $access_token];
    }

    public function expire_service_tokens ($service_id)
    {
        $q = "UPDATE `{$this->table_access}` SET `atoken_expired` = 'Y', `atoken_expired_on` = :dt WHERE `atoken_mservice_id` = :i";
        $s = $this->db->prepare($q);
        $s->bindParam(":i", $service_id);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        if (!$s->execute()) {
            $failure = $this->class_name.'.expire_service_tokens - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        
        return ['status' => true];
    }

    public function insert_access_token ($service_id, $token, $expiration, $expire_previous = false)
    {
        if ($expire_previous) {
            $this->expire_service_tokens($service_id);
        }

        $q = "INSERT INTO `{$this->table_access}` (`atoken_mservice_id`, `atoken_token`, `atoken_expiry`, `atoken_created`) VALUE (:i, :t, :e, :dt)";

        $s = $this->db->prepare($q);
        $s->bindParam(":i", $service_id);
        $s->bindParam(":t", $token);
        $s->bindParam(":e", $expiration);
        $dt = current_date();
        $s->bindParam(":dt", $dt);

        if (!$s->execute()) {
            $failure = $this->class_name.'.insert_access_token - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        
        return ['status' => true, 'atoken_id' => $this->db->lastInsertId()];

    }

}
