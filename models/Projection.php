<?php
class Projection
{
    private $conn;
    private $table = 't_projections';

    public $id;
    public $referentiel_id;
    public $secteur_id;
    public $scenario;
    public $annee;
    public $valeur;
    public $unite;
    public $source;
    public $description;
    public $state;
    public $add_by;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                  (referentiel_id, secteur_id, scenario, annee, valeur, unite, source, description, state, add_by) 
                  VALUES 
                  (:referentiel_id, :secteur_id, :scenario, :annee, :valeur, :unite, :source, :description, :state, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':scenario', $this->scenario);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':valeur', $this->valeur);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':source', $this->source);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':state', $this->state);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT p.*, r.intitule as referentiel_name, s.name as secteur_name
                  FROM " . $this->table . " p 
                  LEFT JOIN t_secteurs s ON p.secteur_id = s.id
                  LEFT JOIN t_referentiel_indicateur r ON p.referentiel_id = r.id
                  ORDER BY p.secteur_id, p.scenario, p.annee ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $query = "SELECT p.*, r.intitule as referentiel_name, s.name as secteur_name
                  FROM " . $this->table . " p 
                  LEFT JOIN t_secteurs s ON p.secteur_id = s.id
                  LEFT JOIN t_referentiel_indicateur r ON p.referentiel_id = r.id
                  WHERE p.state = 'actif'
                  ORDER BY p.secteur_id, p.scenario, p.annee ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function readById()
    {
        $query = "SELECT p.*, r.intitule as referentiel_name, s.name as secteur_name
                  FROM " . $this->table . " p 
                  LEFT JOIN t_secteurs s ON p.secteur_id = s.id
                  LEFT JOIN t_referentiel_indicateur r ON p.referentiel_id = r.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readBySecteur()
    {
        $query = "SELECT p.*, r.intitule as referentiel_name, s.name as secteur_name
                  FROM " . $this->table . " p 
                  LEFT JOIN t_secteurs s ON p.secteur_id = s.id
                  LEFT JOIN t_referentiel_indicateur r ON p.referentiel_id = r.id
                  WHERE p.secteur_id = :secteur_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET 
                  referentiel_id = :referentiel_id,
                  secteur_id = :secteur_id, 
                  scenario = :scenario, 
                  annee = :annee, 
                  valeur = :valeur, 
                  unite = :unite, 
                  source = :source, 
                  description = :description, 
                  state = :state, 
                  add_by = :add_by 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':scenario', $this->scenario);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':valeur', $this->valeur);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':source', $this->source);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':state', $this->state);
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
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteLine()
    {
        $query = "DELETE FROM " . $this->table . " WHERE secteur_id = :secteur_id AND scenario = :scenario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':scenario', $this->scenario);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
