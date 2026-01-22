<?php
class Tache
{
    private $conn;
    private $table = 't_taches';

    public $id;
    public $name;
    public $description;
    public $status;
    public $debut_prevu;
    public $fin_prevue;
    public $debut_reel;
    public $fin_reelle;
    public $code;
    public $projet_id;
    public $assigned_id;
    public $priorite;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, description, status, debut_prevu, fin_prevue, projet_id, assigned_id, priorite, add_by) VALUES 
                  (:code, :name, :description, :status, :debut_prevu, :fin_prevue, :projet_id, :assigned_id, :priorite, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':debut_prevu', $this->debut_prevu);
        $stmt->bindParam(':fin_prevue', $this->fin_prevue);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':assigned_id', $this->assigned_id);
        $stmt->bindParam(':priorite', $this->priorite);
        $stmt->bindParam(':add_by', $this->add_by);


        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function update()
    {
        $query = "UPDATE " . $this->table . " SET 
                  code=:code, name=:name, description=:description, status=:status,
                  debut_prevu=:debut_prevu, fin_prevue=:fin_prevue,
                  debut_reel=:debut_reel, fin_reelle=:fin_reelle,
                  projet_id=:projet_id, assigned_id=:assigned_id,
                  priorite=:priorite, add_by=:add_by
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':debut_prevu', $this->debut_prevu);
        $stmt->bindParam(':fin_prevue', $this->fin_prevue);
        $stmt->bindParam(':debut_reel', $this->debut_reel);
        $stmt->bindParam(':fin_reelle', $this->fin_reelle);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':assigned_id', $this->assigned_id);
        $stmt->bindParam(':priorite', $this->priorite);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function read()
    {
        $query = "SELECT t.*, p.name as projet_name, 
                         CONCAT(u.nom, ' ', u.prenom) as responsable_name
                  FROM " . $this->table . " t
                  LEFT JOIN t_projets p ON t.projet_id = p.id
                  LEFT JOIN t_users u ON t.assigned_id = u.id
                  ORDER BY t.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT t.*, p.name as projet_name, 
                         CONCAT(u.nom, ' ', u.prenom) as responsable_name
                  FROM " . $this->table . " t
                  LEFT JOIN t_projets p ON t.projet_id = p.id
                  LEFT JOIN t_users u ON t.assigned_id = u.id
                  WHERE t.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByProjet()
    {
        $query = "SELECT t.*, p.name as projet_name, 
                         CONCAT(u.nom, ' ', u.prenom) as responsable_name
                  FROM " . $this->table . " t
                  LEFT JOIN t_projets p ON t.projet_id = p.id
                  LEFT JOIN t_users u ON t.assigned_id = u.id
                  WHERE t.projet_id = :projet_id 
                  ORDER BY t.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($status)
    {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
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
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
