<?php
class SectionDash
{
    private $conn;
    private $table = 't_section_dash';

    public $id;
    public $section;
    public $position;
    public $icone;
    public $couleur;
    public $entity_type;
    public $intitule;
    public $entity_id;
    public $add_by;
    

    
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " ( position, icone, couleur, entity_type, entity_id, intitule, add_by) VALUES 
             ( :position, :icone, :couleur, :entity_type, :entity_id, :intitule, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':icone', $this->icone);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':entity_id', $this->entity_id);
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

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET  position=:position, icone=:icone, couleur=:couleur, entity_type=:entity_type, entity_id=:entity_id, intitule=:intitule, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':icone', $this->icone);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateState($state)
    {
        $query = "UPDATE " . $this->table . " SET state=:state WHERE id=:id";
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
