<?php
class Structure
{
    private $conn;
    private $table = 't_structures';

    public $id;
    public $code;
    public $sigle;
    public $logo;
    public $email;
    public $phone;
    public $address;
    public $description;
    public $type_id;
    public $add_by;


    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                 (code, sigle, logo, email, phone, address, description, type_id, add_by) 
                 VALUES (:code, :sigle, :logo, :email, :phone, :address, :description, :type_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type_id', $this->type_id);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function read()
    {
        $query = "SELECT s.*, ts.name as type_name 
                 FROM " . $this->table . " s
                 LEFT JOIN t_type_structures ts ON s.type_id = ts.id
                 ORDER BY s.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT s.*, ts.name as type_name 
                 FROM " . $this->table . " s
                 LEFT JOIN t_type_structures ts ON s.type_id = ts.id
                 WHERE s.id = :id";

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
                     logo = :logo, 
                     email = :email, 
                     phone = :phone, 
                     address = :address, 
                     description = :description, 
                     type_id = :type_id,
                     add_by = :add_by,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':sigle', $this->sigle);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':type_id', $this->type_id);
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

    public function readByType()
    {
        $query = "SELECT s.*, ts.name as type_name 
                 FROM " . $this->table . " s
                 LEFT JOIN t_type_structures ts ON s.type_id = ts.id
                 WHERE s.type_id = :type_id
                 ORDER BY s.name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type_id', $this->type_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateState($state) {
        $query = "UPDATE " . $this->table . " SET state = :state WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
