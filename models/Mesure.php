<?php
class Mesure
{
    private $conn;
    private $table = 't_mesures';

    public $id;
    public $code;
    public $name;
    public $structure_id;
    public $action_type;
    public $status;
    public $gaz;
    public $secteur_id;
    public $annee_debut;
    public $annee_fin;
    public $valeur_realise;
    public $valeur_cible;
    public $description;
    public $objectif;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, structure_id, action_type, status, gaz, secteur_id, annee_debut, annee_fin, valeur_realise, valeur_cible, description, objectif, add_by) VALUES 
                  (:code, :name, :structure_id, :action_type, :status, :gaz, :secteur_id, :annee_debut, :annee_fin, :valeur_realise, :valeur_cible, :description, :objectif, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':annee_debut', $this->annee_debut);
        $stmt->bindParam(':annee_fin', $this->annee_fin);
        $stmt->bindParam(':valeur_realise', $this->valeur_realise);
        $stmt->bindParam(':valeur_cible', $this->valeur_cible);
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

        foreach ($data as &$row) {
            if (isset($row['description'])) {
                $description = html_entity_decode($row['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $description = strip_tags($description);
                $description = str_replace(["\xc2\xa0", "\u{00A0}", "&nbsp;"], ' ', $description);
                $description = trim(preg_replace('/\s+/', ' ', $description));
                $row['description'] = $description;
            }

            if (isset($row['objectif'])) {
                $objectif = html_entity_decode($row['objectif'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $objectif = strip_tags($objectif);
                $objectif = str_replace(["\xc2\xa0", "\u{00A0}", "&nbsp;"], ' ', $objectif);
                $objectif = trim(preg_replace('/\s+/', ' ', $objectif));
                $row['objectif'] = $objectif;
            }
        }

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

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET 
      code=:code, name=:name, structure_id=:structure_id, action_type=:action_type, status=:status, gaz=:gaz, secteur_id=:secteur_id, annee_debut=:annee_debut, annee_fin=:annee_fin, 
      valeur_realise=:valeur_realise, valeur_cible=:valeur_cible, description=:description, objectif=:objectif, add_by=:add_by 
      WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':annee_debut', $this->annee_debut);
        $stmt->bindParam(':annee_fin', $this->annee_fin);
        $stmt->bindParam(':valeur_realise', $this->valeur_realise);
        $stmt->bindParam(':valeur_cible', $this->valeur_cible);
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

    public function createOrUpdateFromSync($data, $secteur_id, $typeAction)
    {
        $id_ref = $data['id'] ?? null;
        if (!$id_ref)
            return;

        $check = $this->conn->prepare("SELECT id FROM projet WHERE id_ref = :id_ref AND secteur_id = :secteur_id");
        $check->execute([':id_ref' => $id_ref, ':secteur_id' => $secteur_id]);

        if ($check->rowCount() > 0) {
            $sql = "UPDATE projet SET intitule=:intitule, cout=:cout, updated_at=NOW() WHERE id_ref=:id_ref AND secteur_id=:secteur_id";
        } else {
            $sql = "INSERT INTO projet (id_ref, intitule, cout, secteur_id, type_action, created_at)
                valeurS (:id_ref, :intitule, :cout, :secteur_id, :type_action, NOW())";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id_ref' => $id_ref,
            ':intitule' => $data['intitule'] ?? '',
            ':cout' => $data['cout'] ?? 0,
            ':secteur_id' => $secteur_id,
            ':type_action' => $typeAction
        ]);
    }
}
