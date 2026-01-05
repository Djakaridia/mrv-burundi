
<?php
class Colline {
    private $conn;
    private $table = 't_collines';

    public $id;
    public $code;
    public $name;
    public $longitude;
    public $latitude;
    public $hommes;
    public $femmes;
    public $jeunes;
    public $adultes;
    public $commune;
    public $add_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (code, name, longitude, latitude, hommes, femmes, jeunes, adultes, commune, add_by) VALUES (:code, :name, :longitude, :latitude, :hommes, :femmes, :jeunes, :adultes, :commune, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':commune', $this->commune);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':latitude', $this->latitude);
        $stmt->bindParam(':hommes', $this->hommes);
        $stmt->bindParam(':femmes', $this->femmes);
        $stmt->bindParam(':jeunes', $this->jeunes);
        $stmt->bindParam(':adultes', $this->adultes);
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

    public function readByCommune() {
        $query = "SELECT * FROM " . $this->table . " WHERE commune = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->commune);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, longitude=:longitude, latitude=:latitude, hommes=:hommes, femmes=:femmes, jeunes=:jeunes, adultes=:adultes, commune=:commune, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':commune', $this->commune);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':latitude', $this->latitude);
        $stmt->bindParam(':hommes', $this->hommes);
        $stmt->bindParam(':femmes', $this->femmes);
        $stmt->bindParam(':jeunes', $this->jeunes);
        $stmt->bindParam(':adultes', $this->adultes);
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
