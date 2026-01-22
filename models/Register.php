<?php
class Register
{
    private $conn;
    private $table = 't_registres';

    // Colonnes de la table
    public $id;
    public $secteur;
    public $code;
    public $categorie;
    public $annee;
    public $unite;
    public $gaz;
    public $emission_annee;
    public $emission_absolue;
    public $emission_niveau;
    public $emission_cumulee;
    public $file;
    public $afficher;
    public $status;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* ===================== CREATE ===================== */
    public function create()
    {
        $query = "INSERT INTO {$this->table}
            (secteur, code, categorie, annee, unite, gaz,
             emission_annee, emission_absolue, emission_niveau, emission_cumulee,
             file, add_by)
            VALUES
            (:secteur, :code, :categorie, :annee, :unite, :gaz,
             :emission_annee, :emission_absolue, :emission_niveau, :emission_cumulee,
             :file, :add_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':secteur', $this->secteur);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':categorie', $this->categorie);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':emission_annee', $this->emission_annee);
        $stmt->bindParam(':emission_absolue', $this->emission_absolue);
        $stmt->bindParam(':emission_niveau', $this->emission_niveau);
        $stmt->bindParam(':emission_cumulee', $this->emission_cumulee);
        $stmt->bindParam(':file', $this->file);
        $stmt->bindParam(':add_by', $this->add_by);

        return $stmt->execute();
    }

    /* ===================== READ ===================== */
    public function read()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY annee DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByCategorie()
    {
        $query = "SELECT categorie, SUM(emission_annee) as emission_annee,SUM(emission_absolue) as emission_absolue,SUM(emission_niveau) as emission_niveau,SUM(emission_cumulee) as emission_cumulee FROM " . $this->table . "  GROUP BY categorie
        ORDER BY categorie DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public function readByGaz()
    {
        $query = "SELECT gaz, SUM(emission_annee) as emission_annee,SUM(emission_absolue) as emission_absolue,SUM(emission_niveau) as emission_niveau,SUM(emission_cumulee) as emission_cumulee FROM " . $this->table . "  GROUP BY gaz
        ORDER BY gaz DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }
    public function readByYear()
    {
        $query = "SELECT annee, SUM(emission_annee) as emission_annee,SUM(emission_absolue) as emission_absolue,SUM(emission_niveau) as emission_niveau,SUM(emission_cumulee) as emission_cumulee FROM " . $this->table . "  GROUP BY annee
        ORDER BY annee DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }
    public function readById()
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByAnnee()
    {
        $query = "SELECT * FROM {$this->table} WHERE annee = :annee";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':annee', $this->annee);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===================== UPDATE ===================== */
    public function update()
    {
        $query = "UPDATE {$this->table} SET
            secteur = :secteur,
            categorie = :categorie,
            unite = :unite,
            gaz = :gaz,
            emission_annee = :emission_annee,
            emission_absolue = :emission_absolue,
            emission_niveau = :emission_niveau,
            emission_cumulee = :emission_cumulee,
            afficher = :afficher,
            status = :status,
            add_by = :add_by
        WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':secteur', $this->secteur);
        $stmt->bindParam(':categorie', $this->categorie);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':gaz', $this->gaz);
        $stmt->bindParam(':emission_annee', $this->emission_annee);
        $stmt->bindParam(':emission_absolue', $this->emission_absolue);
        $stmt->bindParam(':emission_niveau', $this->emission_niveau);
        $stmt->bindParam(':emission_cumulee', $this->emission_cumulee);
        $stmt->bindParam(':afficher', $this->afficher);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /* ===================== UPDATE FILE ===================== */
    public function updateFile()
    {
        $query = "UPDATE {$this->table} SET file = :file WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':file', $this->file);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    /* ===================== DELETE ===================== */
    public function delete()
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
