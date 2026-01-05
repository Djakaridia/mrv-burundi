<?php
class GroupeUsers {
    private $conn;
    private $table = 't_groupe_users';

    public $id;
    public $groupe_id;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (groupe_id, user_id) VALUES (:groupe_id, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->bindParam(':user_id', $this->user_id);

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
        $query = "SELECT * FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByGroupeId() {
        $query = "SELECT * FROM " . $this->table . " WHERE groupe_id=:groupe_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByUserId() {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id=:user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET groupe_id=:groupe_id, user_id=:user_id WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>