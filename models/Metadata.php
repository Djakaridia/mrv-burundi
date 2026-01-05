<?php
class Metadata
{
    private $conn;
    private $table = 't_metadata';

    public $id;
    public $source;
    public $date_ref;
    public $description;
    public $referentiel_id;
    public $add_by;
    
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (source, date_ref, description, referentiel_id, add_by) VALUES (:source, :date_ref, :description, :referentiel_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':source', $this->source);
        $stmt->bindParam(':date_ref', $this->date_ref);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
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

    public function readByReferentielId()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE referentiel_id=:referentiel_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET source=:source, date_ref=:date_ref, description=:description, referentiel_id=:referentiel_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);       
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':source', $this->source);
        $stmt->bindParam(':date_ref', $this->date_ref);   
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':add_by', $this->add_by);

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