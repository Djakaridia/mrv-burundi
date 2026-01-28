<?php
class Suivi
{
    private $conn;
    private $table = 't_suivi_annuelle';

    public $id;
    public $valeur;
    public $annee;
    public $echelle;
    public $classe;
    public $date_suivie;
    public $observation;
    public $scenario;
    public $indicateur_id;
    public $mesure_id;
    public $projet_id;
    public $add_by; 

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (valeur, annee, echelle, classe, date_suivie, observation, scenario, indicateur_id, mesure_id, projet_id, add_by) VALUES 
             (:valeur, :annee, :echelle, :classe, :date_suivie, :observation, :scenario, :indicateur_id, :mesure_id, :projet_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':valeur', $this->valeur);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':echelle', $this->echelle);
        $stmt->bindParam(':classe', $this->classe);
        $stmt->bindParam(':date_suivie', $this->date_suivie);
        $stmt->bindParam(':observation', $this->observation);
        $stmt->bindParam(':scenario', $this->scenario);
        $stmt->bindParam(':indicateur_id', $this->indicateur_id);
        $stmt->bindParam(':mesure_id', $this->mesure_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET valeur=:valeur, annee=:annee, echelle=:echelle, classe=:classe, date_suivie=:date_suivie, observation=:observation, scenario=:scenario, indicateur_id=:indicateur_id, mesure_id=:mesure_id ,projet_id=:projet_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':valeur', $this->valeur);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':echelle', $this->echelle);
        $stmt->bindParam(':classe', $this->classe);
        $stmt->bindParam(':date_suivie', $this->date_suivie);
        $stmt->bindParam(':observation', $this->observation);
        $stmt->bindParam(':scenario', $this->scenario);
        $stmt->bindParam(':indicateur_id', $this->indicateur_id);
        $stmt->bindParam(':mesure_id', $this->mesure_id);
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

    public function readByIndicateur()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE indicateur_id=:indicateur_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':indicateur_id', $this->indicateur_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function readByProjet()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE projet_id=:projet_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
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
