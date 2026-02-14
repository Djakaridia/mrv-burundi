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
    public $gaz;
    public $secteur_id;
    public $programme_id;
    public $groupes;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (logo, code, name, description, objectif, status, budget, start_date, end_date, signature_date, miparcours_date, structure_id, secteur_id, programme_id, groupes, action_type, gaz, add_by) VALUES 
                  (:logo, :code, :name, :description, :objectif, :status, :budget, :start_date, :end_date, :signature_date, :miparcours_date, :structure_id, :secteur_id, :programme_id, :groupes, :action_type, :gaz, :add_by)";

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
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':programme_id', $this->programme_id);
        $stmt->bindParam(':groupes', $this->groupes);
        $stmt->bindParam(':gaz', $this->gaz);
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
                str.sigle as structure_sigle,
                sec.name AS secteur_name,
                pro.name AS programme_name
          FROM " . $this->table . " p 
          LEFT JOIN t_structures str ON p.structure_id = str.id
          LEFT JOIN t_secteurs sec ON p.secteur_id = sec.id
          LEFT JOIN t_programmes pro ON p.programme_id = pro.id
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
      structure_id=:structure_id, secteur_id=:secteur_id, programme_id=:programme_id, groupes=:groupes, action_type=:action_type,
      gaz=:gaz, add_by=:add_by 
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
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':programme_id', $this->programme_id);
        $stmt->bindParam(':groupes', $this->groupes);
        $stmt->bindParam(':gaz', $this->gaz);
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
