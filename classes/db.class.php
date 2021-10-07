<?php

class DB
{
    /*
        Setting values from system constants
    */
    private $host = DB_HOST;
    private $database = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;

    public function credentials ($host, $database, $username, $password)
    {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect()
    {
        $dsn = 'mysql:host=' . $this->host . '; dbname=' . $this->database;
        try {
            $pdo = new PDO($dsn, $this->username, $this->password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
        } catch(Exception $e) {
            if (PROJECT_MODE === 'development') {
                die('E.01: Failure. '.$e->getMessage());
            } else {
                die('E.01: Failure');
            }
            return false;
        }
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
