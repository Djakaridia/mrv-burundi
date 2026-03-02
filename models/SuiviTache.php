<?php
class SuiviTache
{
    private $conn;
    private $table = 't_suivi_taches';

    public $id;
    public $observation;
    public $difficulte;
    public $solution;
    public $date_suivie;
    public $add_by;
    public $status;
    public $tache_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
        (observation, difficulte, solution, date_suivie, add_by, tache_id, status) VALUES 
        (:observation, :difficulte, :solution, :date_suivie, :add_by, :tache_id, :status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':observation', $this->observation);
        $stmt->bindParam(':difficulte', $this->difficulte);
        $stmt->bindParam(':solution', $this->solution);
        $stmt->bindParam(':date_suivie', $this->date_suivie);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':tache_id', $this->tache_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function update()
    {
        $query = "UPDATE " . $this->table .
            " SET observation=:observation, difficulte=:difficulte, solution=:solution, date_suivie=:date_suivie, add_by=:add_by, status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':observation', $this->observation);
        $stmt->bindParam(':difficulte', $this->difficulte);
        $stmt->bindParam(':solution', $this->solution);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':date_suivie', $this->date_suivie);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByTache()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE tache_id = :tache_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tache_id', $this->tache_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
