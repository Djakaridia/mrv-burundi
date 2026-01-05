<?php
class Typologie
{
    private $conn;
    private $table = 't_typologies';

    public $id;
    public $name;
    public $couleur;
    public $referentiel_id;
    public $add_by;
    
    public function __construct($db)
    {
        $this->conn = $db;
    }   

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (name, couleur, referentiel_id, add_by) VALUES (:name, :couleur, :referentiel_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function readById()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row;
    }

    public function readByReferentielId()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE referentiel_id=:referentiel_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET name=:name, couleur=:couleur, referentiel_id=:referentiel_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);       
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':couleur', $this->couleur);   
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}