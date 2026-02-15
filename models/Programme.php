<?php
class Programme
{
    private $conn;
    private $table = 't_programmes';

    public $id;
    public $name;
    public $sigle;
    public $annee_debut;
    public $annee_fin;
    public $description;
    public $code;
    public $status;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (name, sigle, annee_debut, annee_fin, description, code, status, add_by) VALUES (:name, :sigle, :annee_debut, :annee_fin, :description, :code, :status, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':annee_debut', $this->annee_debut);
        $stmt->bindParam(':annee_fin', $this->annee_fin);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':status', $this->status);
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
        $query = "UPDATE " . $this->table . " SET name=:name, sigle=:sigle, annee_debut=:annee_debut, annee_fin=:annee_fin, description=:description, code=:code, status=:status, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':annee_debut', $this->annee_debut);
        $stmt->bindParam(':annee_fin', $this->annee_fin);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':add_by', $this->add_by);

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
