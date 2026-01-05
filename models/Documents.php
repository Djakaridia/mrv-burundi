<?php
class Documents
{
    private $conn;
    private $table = 't_documents';

    public $id;
    public $name;
    public $file_type;
    public $file_path;
    public $file_size;
    public $add_by;
    public $description;
    public $dossier_id;
    public $entity_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (name, file_type, file_path, file_size, add_by, description, dossier_id, entity_id) VALUES 
                 (:name, :file_type, :file_path, :file_size, :add_by, :description, :dossier_id, :entity_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':file_type', $this->file_type);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':file_size', $this->file_size);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':dossier_id', $this->dossier_id);
        $stmt->bindParam(':entity_id', $this->entity_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT d.*, u.nom as uploader_name FROM " . $this->table . " d 
                  LEFT JOIN t_users u ON d.add_by = u.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT d.*, u.nom as uploader_name FROM " . $this->table . " d 
                  LEFT JOIN t_users u ON d.add_by = u.id
                  WHERE d.id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function readByDossier()
    {
        $query = "SELECT d.*, u.nom as uploader_name FROM " . $this->table . " d 
                  LEFT JOIN t_users u ON d.add_by = u.id
                  WHERE d.dossier_id=:dossier_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dossier_id', $this->dossier_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByEntityId()
    {
        $query = "SELECT d.*, u.nom as uploader_name FROM " . $this->table . " d 
                  LEFT JOIN t_users u ON d.add_by = u.id
                  WHERE d.entity_id=:entity_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET name=:name, description=:description, dossier_id=:dossier_id, entity_id=:entity_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':dossier_id', $this->dossier_id);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
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

        return $stmt->execute();
    }
}
