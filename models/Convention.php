<?php
class Convention
{
    private $conn;
    private $table = 't_conventions';

    public $id;
    public $code;
    public $name;
    public $montant;
    public $date_accord;
    public $action_type;
    public $partenaire_id;
    public $projet_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (code, name, montant, date_accord, action_type, partenaire_id, projet_id, add_by) VALUES (:code, :name, :montant, :date_accord, :action_type, :partenaire_id, :projet_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':montant', $this->montant);
        $stmt->bindParam(':date_accord', $this->date_accord);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':partenaire_id', $this->partenaire_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
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

    public function readByPartenaire()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE partenaire_id=:partenaire_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':partenaire_id', $this->partenaire_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function readByProjet()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE projet_id=:projet_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET code=:code, name=:name, montant=:montant, date_accord=:date_accord, action_type=:action_type, partenaire_id=:partenaire_id, projet_id=:projet_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':montant', $this->montant);
        $stmt->bindParam(':date_accord', $this->date_accord);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':partenaire_id', $this->partenaire_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':add_by', $this->add_by);

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
