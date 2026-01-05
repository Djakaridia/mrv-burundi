<?php
class NiveauResultat
{
    private $conn;
    private $table = 't_niveau_resultat';

    public $id;
    public $code;
    public $name;
    public $niveau;
    public $programme;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, niveau, programme, add_by) VALUES 
             (:code, :name, :niveau, :programme, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->bindParam(':programme', $this->programme);
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

    public function readByNiveau()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE niveau=:niveau ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function readByProgramme()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE programme=:programme ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':programme', $this->programme);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, niveau=:niveau, programme=:programme, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->bindParam(':programme', $this->programme);
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
