<?php
class ConventionRIO
{
    private $conn;
    private $table = 't_convention_rio';

    public $id;
    public $code;
    public $programme;
    public $niveau;
    public $referentiel_id;
    public $add_by;
    
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, programme, niveau, referentiel_id, add_by) VALUES (:code, :programme, :niveau, :referentiel_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':programme', $this->programme);
        $stmt->bindParam(':niveau', $this->niveau);
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
        $query = "SELECT * FROM " . $this->table . " WHERE referentiel_id=:referentiel_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET code=:code, programme=:programme, niveau=:niveau, referentiel_id=:referentiel_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);       
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':programme', $this->programme);   
        $stmt->bindParam(':niveau', $this->niveau);
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