<?php

require_once __DIR__ .'/DB.php';
class user {
    private $id ;
    private $name ;
    private $email ;
    private $password ;

    private $pdo ;


    public function __construct($id, $name, $email, $password)
    {
        $this->id = $id ;
        $this->name = $name ;
        $this->email = $email ;
        $this->password = $password ;

        $db = new DB("localhost", "marrakech_food", "root", "");
        $this->pdo = $db->getPDO();
    }

    public function getId(){
        return $this->id ;
    }

    public function getName(){
        return $this->name ;
    }

    public function getEmail(){
        return $this->email ;
    }

    public function getPassword(){
        return $this->password ;
    }

    public function setId($id){
        $this->id = $id ;
    }

    public function setName($name){
        $this->name = $name ;
    }

    public function setEmail($email){
        $this->email = $email ;

    }

    public function setPassword($password){
        $this->password = $password ;
    }



    public function create(){
        try{
            $dsn = 'INSERT INTO user (name, email, password) VALUES (:name, :email, :password)';
            $stmt = $this->pdo->prepare($dsn);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);

        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function findByEmail(){
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM user WHERE email = :email');
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function findById($id){
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM user WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function hashingPassword($password){
        return password_hash($password, PASSWORD_DEFAULT);
    }

}