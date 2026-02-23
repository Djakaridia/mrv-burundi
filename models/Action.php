<?php
class ActionPrioritaire
{
    private $conn;
    private $table = 't_actions_prioritaires';

    public $id;
    public $code;
    public $name;
    public $description;
    public $objectif_wem;
    public $objectif_wam;
    public $action_type;
    public $secteur_id;
    public $sous_secteur_id;
    public $add_by;
    public $state;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table}
                    SET
                        code = :code,
                        name = :name,
                        description = :description,
                        objectif_wem = :objectif_wem,
                        objectif_wam = :objectif_wam,
                        action_type = :action_type,
                        secteur_id = :secteur_id,
                        sous_secteur_id = :sous_secteur_id,
                        add_by = :add_by";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':objectif_wem', $this->objectif_wem);
        $stmt->bindParam(':objectif_wam', $this->objectif_wam);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':sous_secteur_id', $this->sous_secteur_id);
        $stmt->bindParam(':add_by', $this->add_by);
        return $stmt->execute();
    }

    public function read()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE {$this->table}
                    SET
                        code = :code,
                        name = :name,
                        description = :description,
                        objectif_wem = :objectif_wem,
                        objectif_wam = :objectif_wam,
                        action_type = :action_type,
                        secteur_id = :secteur_id,
                        sous_secteur_id = :sous_secteur_id,
                        add_by = :add_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':objectif_wem', $this->objectif_wem);
        $stmt->bindParam(':objectif_wam', $this->objectif_wam);
        $stmt->bindParam(':action_type', $this->action_type);
        $stmt->bindParam(':secteur_id', $this->secteur_id);
        $stmt->bindParam(':sous_secteur_id', $this->sous_secteur_id);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function updateState()
    {
        $query = "UPDATE {$this->table} SET state = :state WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':state', $this->state);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete()
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }
}
