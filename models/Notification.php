<?php
class Notification {
    private $conn;
    private $table = 't_notifications';

    public $id;
    public $titre;
    public $message;
    public $type;
    public $entity_type;
    public $entity_id;
    public $user_id;
    public $add_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (titre, message, type, entity_type, entity_id, user_id, add_by) VALUES 
                 (:titre, :message, :type, :entity_type, :entity_id, :user_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id=:id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByUser() {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id=:user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function readByEntity() {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id=:user_id AND entity_type=:entity_type ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET titre=:titre, message=:message, type=:type, 
                 entity_type=:entity_type, entity_id=:entity_id, user_id=:user_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function markAsRead() {
        $query = "UPDATE " . $this->table . " SET is_read=true WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function markAsStarred($status) {
        $query = "UPDATE " . $this->table . " SET is_starred=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function markAsArchived($status) {
        $query = "UPDATE " . $this->table . " SET is_archived=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>