<?php
class RapportPeriode
{
    private $conn;
    private $table = 't_rapports_periodiques';

    public $id;
    public $code;
    public $intitule;
    public $periode;
    public $mois_ref;
    public $annee_ref;
    public $description;
    public $projet_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, intitule, description, periode, mois_ref, annee_ref, projet_id, add_by) VALUES (:code, :intitule, :description, :periode, :mois_ref, :annee_ref, :projet_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':periode', $this->periode);
        $stmt->bindParam(':mois_ref', $this->mois_ref);
        $stmt->bindParam(':annee_ref', $this->annee_ref);
        $stmt->bindParam(':projet_id', $this->projet_id);
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

    public function readByProjet()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE projet_id=:projet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET code=:code, intitule=:intitule, description=:description, periode=:periode, mois_ref=:mois_ref, annee_ref=:annee_ref, projet_id=:projet_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':periode', $this->periode);
        $stmt->bindParam(':mois_ref', $this->mois_ref);
        $stmt->bindParam(':annee_ref', $this->annee_ref);
        $stmt->bindParam(':projet_id', $this->projet_id);
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
