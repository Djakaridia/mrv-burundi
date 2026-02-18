<?php
class Partenaire
{
    private $conn;
    private $table = 't_partenaires';

    public $id;
    public $code;
    public $sigle;
    public $email;
    public $description;
    public $perimetre;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                 (code, sigle, email, description, perimetre, add_by) 
                 VALUES (:code, :sigle, :email, :description, :perimetre, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':perimetre', $this->perimetre);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " 
                 SET code = :code, 
                     sigle = :sigle, 
                     email = :email, 
                     description = :description, 
                     perimetre = :perimetre,
                     add_by = :add_by,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':perimetre', $this->perimetre);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
