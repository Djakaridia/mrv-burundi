<?php
class Mesure
{
    private $conn;
    private $table = 't_mesures';

    public $id;
    public $code;
    public $name;
    public $referentiel_id;
    public $structure_id;
    public $action_type;
    public $instrument;
    public $status;
    public $gaz;
    public $secteur_id;
    public $annee_debut;
    public $annee_fin;
    public $latitude;
    public $longitude;
    public $description;
    public $objectif;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, referentiel_id, structure_id, action_type, instrument, status, gaz, secteur_id, annee_debut, annee_fin, latitude, longitude, description, objectif, add_by) VALUES 
                  (:code, :name, :referentiel_id, :structure_id, :action_type, :instrument, :status, :gaz, :secteur_id, :annee_debut, :annee_fin, :latitude, :longitude, :description, :objectif, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':instrument', $this->instrument);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':annee_debut', $this->annee_debut);
        $stmt->bindParam(':annee_fin', $this->annee_fin);
        $stmt->bindParam(':latitude', $this->latitude);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':objectif', $this->objectif);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT p.*, s.sigle as structure_sigle
                  FROM " . $this->table . " p 
                  LEFT JOIN t_structures s ON p.structure_id = s.id
                  ORDER BY p.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $query = "SELECT 
            mes.*,
            str.sigle AS structure_sigle,
            sec.name AS secteur_name,
            san.annee,
            COALESCE(SUM(CAST(san.valeur AS DECIMAL(12,2))), 0) AS emission_evitee
        FROM ". $this->table ." mes
        LEFT JOIN t_structures str 
            ON mes.structure_id = str.id
        LEFT JOIN t_secteurs sec
            ON mes.secteur_id = sec.id
        LEFT JOIN t_suivi_annuelle san 
            ON san.mesure_id = mes.id
        WHERE mes.state = 'actif'
        GROUP BY mes.id, san.annee
        ORDER BY mes.id, san.annee;";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT p.*, s.sigle as structure_sigle
                  FROM " . $this->table . " p 
                  LEFT JOIN t_structures s ON p.structure_id = s.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByIndicateur()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE referentiel_id=:referentiel_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }


    public function update()
    {
        $query = "UPDATE " . $this->table . " SET 
      code=:code, name=:name, referentiel_id=:referentiel_id, structure_id=:structure_id, action_type=:action_type, instrument=:instrument, status=:status, gaz=:gaz, secteur_id=:secteur_id, annee_debut=:annee_debut, annee_fin=:annee_fin, 
      latitude=:latitude, longitude=:longitude, description=:description, objectif=:objectif, add_by=:add_by 
      WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':instrument', $this->instrument);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':annee_debut', $this->annee_debut);
        $stmt->bindParam(':annee_fin', $this->annee_fin);
        $stmt->bindParam(':latitude', $this->latitude);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':objectif', $this->objectif);
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
