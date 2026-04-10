<?php


require_once __DIR__ .'/DB.php';

class category {
    public function __construct($id , $name ){
        $this->id = $id ;
        $this->name = $name ;

        $db = new DB("localhost", "marrakech_food", "root", "");
        $this->pdo = $db->getPDO();
    }

    public function create(){
        try{
            $stmt = $this->pdo->prepare('INSERT INTO category (name) VALUES (:name)');
            $stmt->bindParam(':name', $this->name);
            $stmt->execute();
            return true ;

        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();

        }

    }
    public function geAll(){
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM category');
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $categories;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function getById($id){
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM category WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $categorieid = $stmt->fetch(PDO::FETCH_ASSOC);
            return $categorieid;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}

