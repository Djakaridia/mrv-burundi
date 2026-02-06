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
        $query = "SELECT p.*, s.sigle as structure_sigle
          FROM " . $this->table . " p 
          LEFT JOIN t_structures s ON p.structure_id = s.id
          WHERE p.state='actif'
          ORDER BY p.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
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

    // public function createOrUpdateFromSync($data, $secteur_id, $typeAction)
    // {
    //     $id_ref = $data['id'] ?? null;
    //     if (!$id_ref)
    //         return;

    //     $check = $this->conn->prepare("SELECT id FROM projet WHERE id_ref = :id_ref AND secteur_id = :secteur_id");
    //     $check->execute([':id_ref' => $id_ref, ':secteur_id' => $secteur_id]);

    //     if ($check->rowCount() > 0) {
    //         $sql = "UPDATE projet SET intitule=:intitule, cout=:cout, updated_at=NOW() WHERE id_ref=:id_ref AND secteur_id=:secteur_id";
    //     } else {
    //         $sql = "INSERT INTO projet (id_ref, intitule, cout, secteur_id, type_action, created_at)
    //             valeurS (:id_ref, :intitule, :cout, :secteur_id, :type_action, NOW())";
    //     }

    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute([
    //         ':id_ref' => $id_ref,
    //         ':intitule' => $data['intitule'] ?? '',
    //         ':cout' => $data['cout'] ?? 0,
    //         ':secteur_id' => $secteur_id,
    //         ':type_action' => $typeAction
    //     ]);
    // }
}
