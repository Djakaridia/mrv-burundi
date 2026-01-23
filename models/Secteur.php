<?php
class Secteur
{
    private $conn;
    private $table = 't_secteurs';

    public $id;
    public $code;
    public $name;
    public $organisme;
    public $nature;
    public $source;
    public $description;
    public $parent;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, organisme, nature, source, description, parent, add_by) VALUES (:code, :name, :organisme, :nature, :source, :description, :parent, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':organisme', $this->organisme);
        $stmt->bindParam(':nature', $this->nature);
        $stmt->bindParam(':source', $this->source);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':parent', $this->parent);
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
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, organisme=:organisme, nature=:nature, source=:source, description=:description, parent=:parent, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':organisme', $this->organisme);
        $stmt->bindParam(':nature', $this->nature);
        $stmt->bindParam(':source', $this->source);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':parent', $this->parent);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateState($state) {
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
