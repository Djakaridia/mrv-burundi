<?php
class Projet
{
    private $conn;
    private $table = 't_projets';

    public $id;
    public $logo;
    public $code;
    public $name;
    public $description;
    public $objectif;
    public $status;
    public $budget;
    public $start_date;
    public $end_date;
    public $signature_date;
    public $miparcours_date;
    public $structure_id;
    public $action_type;
    public $gaz_type;
    public $secteurs;
    public $groupes;
    public $programmes;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (logo, code, name, description, objectif, status, budget, start_date, end_date, signature_date, miparcours_date, structure_id, secteurs, groupes, programmes, action_type, gaz_type, add_by) VALUES 
                  (:logo, :code, :name, :description, :objectif, :status, :budget, :start_date, :end_date, :signature_date, :miparcours_date, :structure_id, :secteurs, :groupes, :programmes, :action_type, :gaz_type, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':objectif', $this->objectif);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':budget', $this->budget);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':signature_date', $this->signature_date);
        $stmt->bindParam(':miparcours_date', $this->miparcours_date);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':secteurs', $this->secteurs);
        $stmt->bindParam(':groupes', $this->groupes);
        $stmt->bindParam(':programmes', $this->programmes);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':gaz_type', $this->gaz_type);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT p.*, 
                        s.sigle as structure_sigle
                  FROM " . $this->table . " p 
                  LEFT JOIN t_structures s ON p.structure_id = s.id
                  ORDER BY p.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $query = "SELECT p.*, 
                s.sigle as structure_sigle
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
        $query = "SELECT p.*,
                        s.sigle as structure_sigle
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
      logo=:logo, code=:code, name=:name, description=:description, objectif=:objectif,
      status=:status, budget=:budget, start_date=:start_date, end_date=:end_date, 
      signature_date=:signature_date, miparcours_date=:miparcours_date, 
      structure_id=:structure_id, secteurs=:secteurs, groupes=:groupes, programmes=:programmes, action_type=:action_type,
      gaz_type=:gaz_type, add_by=:add_by 
      WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':objectif', $this->objectif);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':budget', $this->budget);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':signature_date', $this->signature_date);
        $stmt->bindParam(':miparcours_date', $this->miparcours_date);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':secteurs', $this->secteurs);
        $stmt->bindParam(':groupes', $this->groupes);
        $stmt->bindParam(':programmes', $this->programmes);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':gaz_type', $this->gaz_type);
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

    public function createOrUpdateFromSync($data, $secteur, $typeAction)
    {
        $id_ref = $data['id'] ?? null;
        if (!$id_ref)
            return;

        $check = $this->conn->prepare("SELECT id FROM projet WHERE id_ref = :id_ref AND secteur = :secteur");
        $check->execute([':id_ref' => $id_ref, ':secteur' => $secteur]);

        if ($check->rowCount() > 0) {
            $sql = "UPDATE projet SET intitule=:intitule, cout=:cout, updated_at=NOW() WHERE id_ref=:id_ref AND secteur=:secteur";
        } else {
            $sql = "INSERT INTO projet (id_ref, intitule, cout, secteur, type_action, created_at)
                VALUES (:id_ref, :intitule, :cout, :secteur, :type_action, NOW())";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id_ref' => $id_ref,
            ':intitule' => $data['intitule'] ?? '',
            ':cout' => $data['cout'] ?? 0,
            ':secteur' => $secteur,
            ':type_action' => $typeAction
        ]);
    }
}
