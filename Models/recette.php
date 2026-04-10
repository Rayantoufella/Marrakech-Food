<?php

require_once __DIR__ . '/DB.php';


class recette {

    private $id ;
    private $name ;
    private $description ;
    private $category_id ;
    private $user_id ;
    public function __construct( $id, $name, $description, $category_id, $user_id) {
        $this->id = $id ;
        $this->name = $name ;
        $this->description = $description ;
        $this->category_id = $category_id ;
        $this->user_id = $user_id ;

        $db = new DB("localhost", "marrakech_food", "root", "");
        $this->pdo = $db->getPDO();
    }

    public function create(){
        try {
            $stmt = $this->pdo->prepare('INSERT INTO recette (name, description, category_id, user_id) VALUES (:name, :description, :category_id, :user_id)');
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            return true ;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
            return false;
        }


    }

    /*public function getById($id){
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $recette = $stmt->fetch(PDO::FETCH_ASSOC);
            return $recette;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }*/

    public function update($id){
        try{
            $stmt = $this->pdo->prepare('UPDATE recette SET name = :name, description = :description, category_id = :category_id, user_id = :user_id WHERE id = :id');

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return true;
        }catch(PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();

        }
    }

    public function delete($id){
        try{
            $stmt = $this->pdo->prepare('DELETE FROM recette WHERE id = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return true;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    public function getByCategory($category_id){
        try{
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE category_id = :id');
            $stmt->bindParam(':id', $category_id);
            $stmt->execute();
            $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $recettes;
        }catch(PDOException $e){
            echo 'Erreur : ' . $e->getMessage();
        }
    }


}