<?php
class TacheCout
{
    private $conn;
    private $table = 't_tache_couts';

    public $id;
    public $montant;
    public $convention;
    public $tache_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (montant, convention, tache_id, add_by) VALUES 
             (:montant, :convention, :tache_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':montant', $this->montant);
        $stmt->bindParam(':convention', $this->convention);
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

    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE tache_id=:tache_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tache_id', $this->tache_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
