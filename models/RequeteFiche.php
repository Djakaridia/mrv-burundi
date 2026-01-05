<?php
class RequeteFiche
{
    private $conn;
    private $table = 't_requete_fiche';

    public $id;
    public $name;
    public $projet_id;
    public $cmr_id;
    public $query;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (name, projet_id, cmr_id, query, add_by) VALUES 
             (:name, :projet_id, :cmr_id, :query, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':cmr_id', $this->cmr_id);
        $stmt->bindParam(':query', $this->query);
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

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET name=:name, projet_id=:projet_id, cmr_id=:cmr_id, query=:query, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);   
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':cmr_id', $this->cmr_id);
        $stmt->bindParam(':query', $this->query);
        $stmt->bindParam(':add_by', $this->add_by);
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
