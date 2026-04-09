<?php


class DB {
    public $pdo;

    public $host = "localhost";
    public $db = "marrakech_food";
    public $user = "root";
    public $password = "";

    public function __construct($host, $db, $user, $password) {
        $this->host = $host;
        $this->db = $db;
        $this->user = $user;
        $this->password = $password;

    }

    public function connect()
    {
        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db;

            $this->pdo = new PDO($dsn, $this->user, $this->password);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->pdo;
        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function getPDO(){
        if($this->pdo == null){
            $this->connect();
        }
        return $this->pdo;
    }


}