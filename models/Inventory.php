<?php
class Inventory
{
    private $conn;
    private $table = 't_inventaires';

    public $id;
    public $name;
    public $annee;
    public $unite;
    public $methode_ipcc;
    public $source_donnees;
    public $description;
    public $viewtable;
    public $file;
    public $status;
    public $afficher;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (name, annee, unite, methode_ipcc, source_donnees, description, viewtable, afficher, add_by) VALUES 
             (:name, :annee, :unite, :methode_ipcc, :source_donnees, :description, :viewtable, :afficher, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':methode_ipcc', $this->methode_ipcc);
        $stmt->bindParam(':source_donnees', $this->source_donnees);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':viewtable', $this->viewtable);
        $stmt->bindParam(':afficher', $this->afficher);
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
        $query = "UPDATE " . $this->table . " SET name=:name, annee=:annee, unite=:unite, methode_ipcc=:methode_ipcc, source_donnees=:source_donnees, description=:description, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':methode_ipcc', $this->methode_ipcc);
        $stmt->bindParam(':source_donnees', $this->source_donnees);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateAffichage($afficher)
    {
        $query = "UPDATE " . $this->table . " SET afficher = CASE WHEN id = :id THEN :afficher ELSE 'non' END";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':afficher', $afficher);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function updateFile()
    {
        $query = "UPDATE " . $this->table . " SET file=:file WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':file', $this->file);
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
            return json_encode(["columns" => $columns, "data" => $rows]);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function AllData()
    {
        try {
            $query = "SELECT viewtable FROM t_inventaires WHERE afficher = :afficher ORDER BY annee DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['afficher' => 'oui']);
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($tables as $data) {
                $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['viewtable']);

                $sql = "SELECT * FROM `$tableName`";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $columns = [];
                for ($i = 0; $i < $stmt->columnCount(); $i++) {
                    $meta = $stmt->getColumnMeta($i);
                    $columns[] = $meta['name'];
                }

                $result[] = ['table' => $tableName,'columns' => $columns,'data' => $rows];
            }

            return $result;
        } catch (Throwable $th) {
            return false;
        }
    }

    public function AllDataParSecteur()
    {
        try {
            $query = "SELECT viewtable FROM t_inventaires WHERE afficher = :afficher ORDER BY annee DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['afficher' => 'oui']);
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($tables as $data) {
                $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['viewtable']);

                if (empty($tableName))  continue;

                $sql = "SELECT SUM(agriculture) AS agriculture, SUM(fat) AS fat, SUM(energie) AS energie, SUM(dechets) AS dechets FROM `$tableName`";
                $stmtData = $this->conn->prepare($sql);
                $stmtData->execute();
                $rows = $stmtData->fetch(PDO::FETCH_ASSOC);
                $result[] = ['table' => $tableName,'data'  => $rows];
            }

            return $result;
        } catch (Throwable $th) {
            return false;
        }
    }

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
