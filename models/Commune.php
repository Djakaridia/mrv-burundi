
<?php
class Commune {
    private $conn;
    private $table = 't_communes';

    public $id;
    public $code;
    public $name;
    public $province;
    public $add_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (code, name, province, add_by) VALUES (:code, :name, :province, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':province', $this->province);
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

    public function readByProvince() {
        $query = "SELECT * FROM " . $this->table . " WHERE province = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->province);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, province=:province, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':province', $this->province);
        $stmt->bindParam(':add_by', $this->add_by);
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
