<?php
class Indicateur
{
    private $conn;
    private $table = 't_indicateur_cmr';

    // Propriétés correspondant aux colonnes de la table
    public $id;
    public $code;
    public $intitule;
    public $description;
    public $annee_reference;
    public $valeur_reference;
    public $valeur_cible;
    public $unite;
    public $mode_calcul;
    public $responsable;
    public $latitude;
    public $longitude;
    public $referentiel_id;
    public $resultat_id;
    public $projet_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                 (code, intitule, description, unite, mode_calcul, responsable, annee_reference, 
                  valeur_reference, valeur_cible, latitude, longitude, 
                  referentiel_id, resultat_id, projet_id, add_by) 
                 VALUES 
                 (:code, :intitule, :description, :unite, :mode_calcul, :responsable, :annee_reference, 
                  :valeur_reference, :valeur_cible, :latitude, :longitude, 
                  :referentiel_id, :resultat_id, :projet_id, :add_by)";

        $stmt = $this->conn->prepare($query);

        // Liaison des paramètres
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':mode_calcul', $this->mode_calcul);
        $stmt->bindParam(':responsable', $this->responsable);
        $stmt->bindParam(':annee_reference', $this->annee_reference);
        $stmt->bindParam(':valeur_reference', $this->valeur_reference);
        $stmt->bindParam(':valeur_cible', $this->valeur_cible);
        $stmt->bindParam(':latitude', $this->latitude);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':resultat_id', $this->resultat_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id ASC";
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

    public function readByResultat()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE resultat_id = :resultat_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':resultat_id', $this->resultat_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByProjet()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE projet_id = :projet_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':projet_id', $this->projet_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByReferentiel()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE referentiel_id = :referentiel_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " 
                  SET code = :code,
                      intitule = :intitule,
                      description = :description,
                      unite = :unite,
                      mode_calcul = :mode_calcul,
                      responsable = :responsable,
                      annee_reference = :annee_reference,
                      valeur_reference = :valeur_reference,
                      valeur_cible = :valeur_cible,
                      latitude = :latitude,
                      longitude = :longitude,
                      referentiel_id = :referentiel_id,
                      resultat_id = :resultat_id,
                      projet_id = :projet_id,
                      add_by = :add_by,
                      updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Liaison des paramètres
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':intitule', $this->intitule);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':unite', $this->unite);
        $stmt->bindParam(':mode_calcul', $this->mode_calcul);
        $stmt->bindParam(':responsable', $this->responsable);
        $stmt->bindParam(':annee_reference', $this->annee_reference);
        $stmt->bindParam(':valeur_reference', $this->valeur_reference);
        $stmt->bindParam(':valeur_cible', $this->valeur_cible);
        $stmt->bindParam(':latitude', $this->latitude);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':referentiel_id', $this->referentiel_id);
        $stmt->bindParam(':resultat_id', $this->resultat_id);
        $stmt->bindParam(':projet_id', $this->projet_id);
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


    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
