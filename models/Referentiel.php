<?php
class Referentiel
{
    private $conn;
    private $table = 't_referentiel_indicateur';

    // Propriétés correspondant aux colonnes de la table
    public $id;
    public $code;
    public $intitule;
    public $description;
    public $categorie;
    public $norme;
    public $unite;
    public $echelle;
    public $modele;
    public $domaine;
    public $responsable;
    public $autre_responsable;
    public $fonction_agregation;
    public $seuil_min;
    public $seuil_max;
    public $sens_evolution;
    public $in_dashboard;
    public $state;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Créer un nouveau référentiel
    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                 (code, intitule, description, categorie, norme, unite, echelle, modele, domaine, responsable, autre_responsable, fonction_agregation, seuil_min, seuil_max, sens_evolution, in_dashboard, add_by) 
                  VALUES 
                 (:code, :intitule, :description, :categorie, :norme, :unite, :echelle, :modele, :domaine, :responsable, :autre_responsable, :fonction_agregation, :seuil_min, :seuil_max, :sens_evolution, :in_dashboard, :add_by)";

        $stmt = $this->conn->prepare($query);

        // Liaison des paramètres
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':categorie', $this->categorie);
        $stmt->bindParam(':norme', $this->norme);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':echelle', $this->echelle);
        $stmt->bindParam(':modele', $this->modele);
        $stmt->bindParam(':domaine', $this->domaine);
        $stmt->bindParam(':responsable', $this->responsable);
        $stmt->bindParam(':autre_responsable', $this->autre_responsable);
        $stmt->bindParam(':fonction_agregation', $this->fonction_agregation);
        $stmt->bindParam(':seuil_min', $this->seuil_min);
        $stmt->bindParam(':seuil_max', $this->seuil_max);
        $stmt->bindParam(':sens_evolution', $this->sens_evolution);
        $stmt->bindParam(':in_dashboard', $this->in_dashboard);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    // Lire tous les référentiels
    public function read()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire un seul référentiel
    public function readById()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un référentiel
    public function update()
    {
        $query = "UPDATE " . $this->table . " 
                 SET code = :code,
                     intitule = :intitule,
                     description = :description,
                     categorie = :categorie,
                     norme = :norme,
                     unite = :unite,
                     echelle = :echelle,
                     modele = :modele,
                     domaine = :domaine,
                     responsable = :responsable,
                     autre_responsable = :autre_responsable,
                     fonction_agregation = :fonction_agregation,
                     seuil_min = :seuil_min,
                     seuil_max = :seuil_max,
                     sens_evolution = :sens_evolution,
                     in_dashboard = :in_dashboard,
                     add_by = :add_by,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':categorie', $this->categorie);
        $stmt->bindParam(':norme', $this->norme);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':echelle', $this->echelle);
        $stmt->bindParam(':modele', $this->modele);
        $stmt->bindParam(':domaine', $this->domaine);
        $stmt->bindParam(':responsable', $this->responsable);
        $stmt->bindParam(':autre_responsable', $this->autre_responsable);
        $stmt->bindParam(':fonction_agregation', $this->fonction_agregation);
        $stmt->bindParam(':seuil_min', $this->seuil_min);
        $stmt->bindParam(':seuil_max', $this->seuil_max);
        $stmt->bindParam(':sens_evolution', $this->sens_evolution);
        $stmt->bindParam(':in_dashboard', $this->in_dashboard);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateState($state)
    {
        $query = "UPDATE " . $this->table . " SET state = :state WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un référentiel
    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Rechercher des référentiels
    public function search($keywords)
    {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE code LIKE ? OR intitule LIKE ? OR description LIKE ?
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        $keywords = "%{$keywords}%";
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        $stmt->execute();
        return $stmt;
    }
}
