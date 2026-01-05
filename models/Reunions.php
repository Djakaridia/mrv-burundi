<?php
class Reunion
{
    private $conn;
    private $table = 't_reunions';

    public $id;
    public $code;
    public $name;
    public $description;
    public $horaire;
    public $couleur;
    public $url;
    public $lieu;
    public $status;
    public $groupe_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
            (code, name, description, horaire, couleur, lieu, status, groupe_id, add_by) 
            VALUES 
            (:code, :name, :description, :horaire, :couleur, :lieu, :status, :groupe_id, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':horaire', $this->horaire);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':lieu', $this->lieu);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function read()
    {
        $query = "SELECT r.*, g.name as groupe_nom 
                  FROM " . $this->table . " r 
                  LEFT JOIN t_groupe_travail g ON r.groupe_id = g.id 
                  ORDER BY r.updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT r.*, g.name as groupe_nom 
                  FROM " . $this->table . " r 
                  LEFT JOIN t_groupe_travail g ON r.groupe_id = g.id 
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByGroupe()
    {
        $query = "SELECT r.*, g.name as groupe_nom 
                  FROM " . $this->table . " r 
                  LEFT JOIN t_groupe_travail g ON r.groupe_id = g.id 
                  WHERE r.groupe_id = :groupe_id 
                  ORDER BY r.horaire DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name,
                      code = :code,
                      description = :description, 
                      horaire = :horaire, 
                      couleur = :couleur, 
                      lieu = :lieu, 
                      status = :status, 
                      groupe_id = :groupe_id, 
                      updated_at = CURRENT_TIMESTAMP,
                      add_by = :add_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':horaire', $this->horaire);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':lieu', $this->lieu);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function updateState($state)
    {
        $query = "UPDATE " . $this->table . " SET state = :state WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
