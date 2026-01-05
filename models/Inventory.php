<?php
class Inventory
{
    private $conn;
    private $table = 't_inventaires';

    public $id;
    public $name;
    public $annee;
    public $description;
    public $viewtable;
    public $file;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (name, annee, description, viewtable, add_by) VALUES 
             (:name, :annee, :description, :viewtable, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':viewtable', $this->viewtable);
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

    public function readByAnnee()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE annee=:annee";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET name=:name, annee=:annee, description=:description, file=:file WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':file', $this->file);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);

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

    // Read data from viewtable
    public function readData($viewtable)
    {
        try {
            $sql = "SELECT * FROM $viewtable LIMIT 50";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = [];
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $meta = $stmt->getColumnMeta($i);
                $columns[] = $meta['name'];
            }
            return json_encode(["columns" => $columns, "data"    => $rows]);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    // Delete data from viewtable
    public function deleteData($viewtable)
    {
        try {
            $query = "DELETE FROM " . $viewtable;
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                $query = "DROP TABLE IF EXISTS $viewtable";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
            }
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
