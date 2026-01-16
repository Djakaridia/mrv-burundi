<?php
class NiveauIndicateur
{
    private $conn;
    private $table_name = "t_niveau_indicateur";

    public $id;
    public $type;
    public $intitule;
    public $unite;
    public $cibles;
    public $resultat;
    public $add_by;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (type, intitule, unite, cibles, resultat, add_by, created_at) VALUES (:type, :intitule, :unite, :cibles, :resultat, :add_by, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":intitule", $this->intitule);
        $stmt->bindParam(":unite", $this->unite);
        $stmt->bindParam(":resultat", $this->resultat);
        $stmt->bindParam(":add_by", $this->add_by);
        $cibles = !empty($this->cibles) ? $this->cibles : null;
        $stmt->bindParam(":cibles", $cibles);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readById()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByResultat(){
        $query = "SELECT * FROM " . $this->table_name . " WHERE resultat = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->resultat);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET type = :type, intitule = :intitule, unite = :unite, cibles = :cibles, resultat = :resultat, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':resultat', $this->resultat);
        $stmt->bindParam(':id', $this->id);
        $cibles = !empty($this->cibles) ? $this->cibles : null;
        $stmt->bindParam(':cibles', $cibles);
        $stmt->bindParam(':updated_at', $this->updated_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
