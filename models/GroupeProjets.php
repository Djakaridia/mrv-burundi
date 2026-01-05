<?php
class GroupeProjets {
    private $conn;
    private $table = 't_groupe_projets';

    public $id;
    public $projet_id;
    public $groupe_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (projet_id, groupe_id) VALUES (:projet_id, :groupe_id)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':groupe_id', $this->groupe_id);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET projet_id=:projet_id, groupe_id=:groupe_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT gp.*, p.name as projet_nom, g.name as groupe_nom 
                 FROM " . $this->table . " gp 
                 LEFT JOIN projets p ON gp.projet_id = p.id 
                 LEFT JOIN groupe_travail g ON gp.groupe_id = g.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById() {
        $query = "SELECT gp.*, p.name as projet_nom, p.description, p.statut 
                 FROM " . $this->table . " gp 
                 LEFT JOIN projets p ON gp.projet_id = p.id 
                 WHERE gp.id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function readByGroupe() {
        $query = "SELECT gp.*, p.name as projet_nom, p.description, p.statut 
                 FROM " . $this->table . " gp 
                 LEFT JOIN projets p ON gp.projet_id = p.id 
                 WHERE gp.groupe_id=:groupe_id";
        $groupe_id = $this->groupe_id;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':groupe_id', $groupe_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByProjet() {
        $query = "SELECT gp.*, g.name as groupe_nom 
                 FROM " . $this->table . " gp 
                 LEFT JOIN groupe_travail g ON gp.groupe_id = g.id 
                 WHERE gp.projet_id=:projet_id";
        $projet_id = $this->projet_id;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $projet_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " 
                 WHERE projet_id=:projet_id AND id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>