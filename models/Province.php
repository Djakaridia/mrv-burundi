
<?php
class Province {
    private $conn;
    private $table = 't_provinces';

    public $id;
    public $code;
    public $name;
    public $sigle;
    public $couleur;
    public $add_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (code, name, sigle, couleur, add_by) VALUES (:code, :name, :sigle, :couleur, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':couleur', $this->couleur);
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
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, sigle=:sigle, couleur=:couleur, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':couleur', $this->couleur);
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
