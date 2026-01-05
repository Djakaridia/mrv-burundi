<?php
class SuiviTache
{
    private $conn;
    private $table = 't_suivi_taches';

    public $id;
    public $code;
    public $name;
    public $description;
    public $etat_avancement;
    public $difficulte;
    public $solution;
    public $date_suivi;
    public $add_by;
    public $status;
    public $tache_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, description, etat_avancement, difficulte, solution, date_suivi, add_by, tache_id, status) 
                  VALUES (:code, :name, :description, :etat_avancement, :difficulte, :solution, :date_suivi, :add_by, :tache_id, :status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':etat_avancement', $this->etat_avancement);
        $stmt->bindParam(':difficulte', $this->difficulte);
        $stmt->bindParam(':solution', $this->solution);
        $stmt->bindParam(':date_suivi', $this->date_suivi);
        if (empty($this->date_suivi)) {
            $this->date_suivi = date('Y-m-d H:i:s');
        }
        $stmt->bindParam(':date_suivi', $this->date_suivi);
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
            " SET code=:code, name=:name, description=:description, 
                  etat_avancement=:etat_avancement, difficulte=:difficulte, 
                  solution=:solution, date_suivi=:date_suivi, add_by=:add_by,
                  status=:status
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':etat_avancement', $this->etat_avancement);
        $stmt->bindParam(':difficulte', $this->difficulte);
        $stmt->bindParam(':solution', $this->solution);
        $stmt->bindParam(':status', $this->status);
        if (empty($this->date_suivi)) {
            $this->date_suivi = date('Y-m-d H:i:s');
        }
        $stmt->bindParam(':date_suivi', $this->date_suivi);
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
