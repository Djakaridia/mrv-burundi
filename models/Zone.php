
<?php
class Zone {
    private $conn;
    private $table = 't_zones';

    public $id;
    public $code;
    public $name;
    public $superficie;
    public $couches;
    public $couleur;
    public $afficher;
    public $description;
    public $type_id;
    public $add_by;
    public $state;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (code, name, superficie, couches, couleur, afficher, description, type_id, add_by) VALUES (:code, :name, :superficie, :couches, :couleur, :afficher, :description, :type_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':superficie', $this->superficie);
        $stmt->bindParam(':couches', $this->couches);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':afficher', $this->afficher);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type_id', $this->type_id);
        $stmt->bindParam(':add_by', $this->add_by);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT t_zones.* , t_type_zones.name as type_name FROM " . $this->table . " LEFT JOIN t_type_zones ON t_zones.type_id = t_type_zones.id ORDER BY t_zones.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;	        
    }

    public function readById() {
        $query = "SELECT t_zones.* , t_type_zones.name as type_name FROM " . $this->table . " LEFT JOIN t_type_zones ON t_zones.type_id = t_type_zones.id WHERE t_zones.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function readByTypeId() {
        $query = "SELECT t_zones.* , t_type_zones.name as type_name FROM " . $this->table . " LEFT JOIN t_type_zones ON t_zones.type_id = t_type_zones.id WHERE type_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->type_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, superficie=:superficie, couches=:couches, couleur=:couleur, afficher=:afficher, description=:description, type_id=:type_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':superficie', $this->superficie);
        $stmt->bindParam(':couches', $this->couches);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':afficher', $this->afficher);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type_id', $this->type_id);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function updateState() {
        $query = "UPDATE " . $this->table . " SET state=:state WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':state', $this->state);
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
