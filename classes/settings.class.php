<?php

class Settings
{
    
    private $db;
    private $settings;
    private $logs;

    public function __construct(PDO $db) {
        $this->logs = new Logs($db);
        $this->db = $db;
        $this->settings = $this->get_settings()['data'];
    }
    
    private function get_settings()
    {
        $q = 'SELECT * FROM `settings`';
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = 'Settings.get_settings - E.02: Failure';
            $this->logs->create('settings_class', $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'message' => $failure];
        }
        $d = $s->fetchAll();
        $arr = [];
        foreach ($d as $a) {
            $arr[$a['setting_name']] = $a['setting_value'];
        }
        return ['status' => true, 'data' => $arr];
    }

    public function get_all()
    {
        return $this->settings;
    }
    
    public function get_one($setting_name)
    {
        $q = 'SELECT * FROM `settings` WHERE `setting_name`=:setting_name';
        $s = $this->db->prepare($q);
        $s->bindParam(':setting_name', $setting_name);

        if (!$s->execute()) {
            $failure = 'Settings.get_one - E.02: Failure';
            $this->logs->create('settings_class', $failure, json_encode($s->errorInfo()));
            return ['status'=>false, 'message'=>$failure];
        }
        $d = $s->fetch();
        if(count($d) > 0) {
            return ['status' => true, 'data' => $d['setting_value']];
        }
        return ['status' => true, 'data' => ''];
    }

    public function fetch(string $key)
    {
        if (!array_key_exists($key, $this->settings)) {
            $this->logs->create('settings_fetch', $key.' was undefined in settings array');
            return "";
        }
        return $this->settings[$key];
    }

    
    public function protocol() {
        return $this->fetch('protocol');
    }

    public function site_url()
    {
        return $this->fetch('site_url');
    }
    
    public function url() {
        return $this->protocol().'://'.$this->site_url();
    }
    
}
