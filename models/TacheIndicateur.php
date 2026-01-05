<?php
class TacheIndicateur
{
    private $conn;
    private $table = 't_tache_indicateurs';

    public $id;
    public $code;
    public $name;
    public $unite;
    public $valeur_cible;
    public $description;
    public $tache_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, description, unite, valeur_cible, tache_id, add_by) VALUES 
             (:code, :name, :description, :unite, :valeur_cible, :tache_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':valeur_cible', $this->valeur_cible);
        $stmt->bindParam(':tache_id', $this->tache_id);
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

    public function readByTache()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE tache_id=:tache_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tache_id', $this->tache_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, description=:description, unite=:unite, valeur_cible=:valeur_cible, tache_id=:tache_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':valeur_cible', $this->valeur_cible);
        $stmt->bindParam(':tache_id', $this->tache_id);
        $stmt->bindParam(':id', $this->id);

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
