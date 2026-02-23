<?php
class FacteurEmission
{
    private $conn;
    private $table = 't_facteur_emission';

    public $id;
    public $name;
    public $unite;
    public $type;
    public $gaz;
    public $valeur;
    public $referentiel_id;
    public $projet_id;
    public $mesure_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO {$this->table}
                  (name, unite, type , gaz, valeur, referentiel_id, projet_id, mesure_id, add_by) VALUES
                  (:name, :unite, :type , :gaz, :valeur, :referentiel_id, :projet_id, :mesure_id, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':valeur', $this->valeur);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':mesure_id', $this->mesure_id);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function update()
    {
        $query = "UPDATE {$this->table} SET
                    name = :name,
                    unite = :unite,
                    type = :type,
                    gaz = :gaz,
                    valeur = :valeur,
                    referentiel_id = :referentiel_id,
                    projet_id = :projet_id,
                    mesure_id = :mesure_id,
                    add_by = :add_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':valeur', $this->valeur);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':mesure_id', $this->mesure_id);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    public function read()
    {
        $query = "SELECT f.*,
                         p.name AS projet_name,
                         r.name AS referentiel_name,
                         m.name AS mesure_name,
                         CONCAT(u.nom,' ',u.prenom) AS user_name
                  FROM {$this->table} f
                  LEFT JOIN t_projets p ON f.projet_id = p.id
                  LEFT JOIN t_referentiels r ON f.referentiel_id = r.id
                  LEFT JOIN t_mesures m ON f.mesure_id = m.id
                  LEFT JOIN t_users u ON f.add_by = u.id
                  ORDER BY f.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = $query = "SELECT * FROM {$this->table}
                  WHERE id = :id
                  ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByProjet()
    {
        $query = "SELECT * FROM {$this->table}
                  WHERE projet_id = :projet_id
                  ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByMesure()
    {
        $query = "SELECT * FROM {$this->table}
              WHERE mesure_id = :mesure_id
              ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mesure_id', $this->mesure_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByReferentiel()
    {
        $query = "SELECT * FROM {$this->table}
              WHERE referentiel_id = :referentiel_id
              ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete()
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }
}
