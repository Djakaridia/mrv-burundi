<?php
class Role {
    private $conn;
    private $table = 't_roles';

    public $id;
    public $name;
    public $niveau;
    public $description;
    public $page_edit;
    public $page_delete;
    public $page_validate;
    public $page_interdite;
    public $add_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (name, niveau, description, add_by) VALUES (:name, :niveau, :description, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':add_by', $this->add_by);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;	        
    }

    public function readById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET name=:name, niveau=:niveau, description=:description, page_edit=:page_edit, page_delete=:page_delete, page_validate=:page_validate, page_interdite=:page_interdite WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':page_edit', $this->page_edit);
        $stmt->bindParam(':page_delete', $this->page_delete);
        $stmt->bindParam(':page_validate', $this->page_validate);
        $stmt->bindParam(':page_interdite', $this->page_interdite);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }
}
?>
