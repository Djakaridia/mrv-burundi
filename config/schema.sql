-- Table des dashboards sections
CREATE TABLE IF NOT EXISTS t_section_dash (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intitule VARCHAR(200) NOT NULL,
    icone VARCHAR(50),
    couleur VARCHAR(50),
    position INT,
    entity_type VARCHAR(50), -- indicateur, projet
    entity_id INT,  -- id de l'indicateur ou du projet
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des inventaires
CREATE TABLE IF NOT EXISTS t_inventaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    annee INT NOT NULL UNIQUE,
    unite VARCHAR(50),
    methode_ipcc VARCHAR(200),
    source_donnees TEXT,
    description TEXT,
    viewtable TEXT,
    file TEXT,
    afficher VARCHAR(20) DEFAULT 'oui',
    status VARCHAR(20) DEFAULT 'invalide',
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des resgistre carbone
CREATE TABLE IF NOT EXISTS t_registres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secteur VARCHAR(50) NOT NULL,
    categorie VARCHAR(200) NOT NULL,
    annee INT NOT NULL,
    unite VARCHAR(50),
    gaz VARCHAR(20),
    emission_annee DECIMAL(15,2),
    emission_absolue DECIMAL(15,2),
    emission_niveau DECIMAL(15,2),
    emission_cumulee DECIMAL(15,2),
    file TEXT,
    afficher VARCHAR(20) DEFAULT 'oui',
    status VARCHAR(20) DEFAULT 'invalide',
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_gaz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,        -- CO2, CH4, N2O
    couleur VARCHAR(20),
    description TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des régions
CREATE TABLE IF NOT EXISTS t_provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    sigle VARCHAR(50),
    couleur VARCHAR(50),
    couches TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des communes
CREATE TABLE IF NOT EXISTS t_communes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    province VARCHAR(50),
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des villages
CREATE TABLE IF NOT EXISTS t_collines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    commune VARCHAR(50),
    longitude VARCHAR(50),
    latitude VARCHAR(50),
    population INT,
    hommes INT,
    femmes INT,
    jeunes INT,
    adultes INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des types de zone
CREATE TABLE IF NOT EXISTS t_type_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des zones de collectes
CREATE TABLE IF NOT EXISTS t_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    superficie VARCHAR(50),
    couches VARCHAR(50),
    couleur VARCHAR(20),
    afficher VARCHAR(20) DEFAULT 'oui',,
    description TEXT,
    type_id INT,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table de type structures
CREATE TABLE IF NOT EXISTS t_type_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des structures
CREATE TABLE IF NOT EXISTS t_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    sigle VARCHAR(50) NOT NULL,
    logo VARCHAR(200),
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(200),
    address VARCHAR(200),
    description TEXT,
    add_by INT,
    type_id INT,
    secteur_id INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des rôles
CREATE TABLE IF NOT EXISTS t_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    niveau VARCHAR(5) DEFAULT '4', -- 1: Super Admin, 2: Admin, 3: Editeur, 4: Visiteur
    page_edit TEXT,
    page_delete TEXT,
    page_interdite TEXT,
    description TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS t_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    validity TINYINT(1) DEFAULT TRUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(100),
    fonction VARCHAR(50) DEFAULT 'simple',
    role_id INT,
    structure_id INT,
    changer_mot_de_passe TINYINT(1) DEFAULT false,
    nb_tentatives_echoues INT DEFAULT 0,
    stat_changed_date DATETIME NULL DEFAULT NULL,
    stat_changed_auto TINYINT(1) DEFAULT false,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des connexions
CREATE TABLE IF NOT EXISTS t_connexions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT REFERENCES t_users(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE,
    ip VARCHAR(50),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des secteurs
CREATE TABLE IF NOT EXISTS t_secteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    organisme VARCHAR(200) NOT NULL,
    domaine VARCHAR(200) NOT NULL,
    source TEXT,
    description TEXT,
    parent INT DEFAULT 0,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des actions prioritaires
CREATE TABLE IF NOT EXISTS t_actions_prioritaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    objectif TEXT,
    secteur_id INT,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des priorités
CREATE TABLE IF NOT EXISTS t_priorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    couleur VARCHAR(10),
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des unités
CREATE TABLE IF NOT EXISTS t_unites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des dossiers
CREATE TABLE IF NOT EXISTS t_dossiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT,
    parent INT DEFAULT 0,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des documents
CREATE TABLE IF NOT EXISTS t_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size DOUBLE PRECISION DEFAULT 0,
    description TEXT,
    add_by INT,
    entity_id INT DEFAULT 0,
    dossier_id INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des programmes
CREATE TABLE IF NOT EXISTS t_programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    sigle VARCHAR(50) NOT NULL,
    description TEXT,
    status VARCHAR(50),
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des projets
CREATE TABLE IF NOT EXISTS t_projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logo VARCHAR(200),
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    objectif TEXT,
    status VARCHAR(50),
    budget DECIMAL(15,2),
    start_date DATE,
    end_date DATE,
    signature_date DATE,
    miparcours_date DATE,
    secteurs VARCHAR(200),
    groupes VARCHAR(200),
    programmes VARCHAR(200),
    add_by INT,
    structure_id INT,
    priorites_id INT,
    action_id INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des conventions
CREATE TABLE IF NOT EXISTS t_conventions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_accord DATE NOT NULL,
    structure_id INT,
    projet_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des tâches
CREATE TABLE IF NOT EXISTS t_taches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'En attente',
    debut_prevu DATETIME NULL DEFAULT NULL,
    fin_prevue DATETIME NULL DEFAULT NULL,
    debut_reel DATETIME NULL DEFAULT NULL,
    fin_reelle DATETIME NULL DEFAULT NULL,
    projet_id INT,
    assigned_id INT,
    priorites_id INT,
    state VARCHAR(20) DEFAULT 'actif',
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des indicateurs des activités
CREATE TABLE IF NOT EXISTS t_tache_indicateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    unite VARCHAR(50) NOT NULL,
    valeur_cible VARCHAR(50) NOT NULL,
    description TEXT,
    tache_id INT NOT NULL,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des suivi des activités
CREATE TABLE IF NOT EXISTS t_tache_indicateurs_suivi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    valeur_suivi VARCHAR(50) NOT NULL,
    tache_id INT NOT NULL,
    indicateur_id INT NOT NULL,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des couts des activités
CREATE TABLE IF NOT EXISTS t_tache_couts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15,2) NOT NULL,
    convention INT NOT NULL,
    tache_id INT NOT NULL,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des groupes de travail
CREATE TABLE IF NOT EXISTS t_groupe_travail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    monitor INT,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table de liaison entre les groupes et les utilisateurs
CREATE TABLE IF NOT EXISTS t_groupe_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groupe_id INT,
    user_id INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table de liaison entre les projets et les groupes
CREATE TABLE IF NOT EXISTS t_groupe_projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT,
    groupe_id INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des réunions de groupes
CREATE TABLE IF NOT EXISTS t_reunions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    horaire DATETIME NULL DEFAULT NULL,
    couleur VARCHAR(50),
    lieu VARCHAR(50),
    status VARCHAR(50),
    groupe_id INT,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des référentiels d'indicateurs
CREATE TABLE IF NOT EXISTS t_referentiel_indicateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    intitule VARCHAR(200) NOT NULL,
    description TEXT,
    categorie VARCHAR(30),
    norme VARCHAR(30),
    unite VARCHAR(50),
    domaine VARCHAR(30),
    action VARCHAR(30),
    echelle VARCHAR(100),
    modele VARCHAR(100),
    fonction_agregation VARCHAR(50),
    responsable VARCHAR(30),
    autre_responsable VARCHAR(50),
    seuil_min DOUBLE PRECISION DEFAULT 0,
    seuil_max DOUBLE PRECISION DEFAULT 0,
    sens_evolution VARCHAR(50),
    in_dashboard TINYINT(1) DEFAULT false,
    state VARCHAR(20) DEFAULT 'actif',
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des typologies des indicateurs
CREATE TABLE IF NOT EXISTS t_typologies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL UNIQUE,
    couleur VARCHAR(10),
    referentiel_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des metadata des indicateurs
CREATE TABLE IF NOT EXISTS t_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(50),
    date_ref DATE,
    description TEXT,
    referentiel_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des convention RIO
CREATE TABLE IF NOT EXISTS t_convention_rio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    programme INT,
    niveau INT,
    referentiel_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des niveau de resultat
CREATE TABLE IF NOT EXISTS t_niveau (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type VARCHAR(50),
    level VARCHAR(50) UNIQUE,
    programme INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des objectifs de niveau de resultat
CREATE TABLE IF NOT EXISTS t_niveau_resultat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name TEXT,
    niveau INT,
    parent INT,
    programme INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des indicateur de niveau de resultat
CREATE TABLE IF NOT EXISTS t_niveau_indicateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    intitule TEXT,
    unite VARCHAR(50),
    resultat INT,
    cibles TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des indicateurs CMR
CREATE TABLE IF NOT EXISTS t_indicateur_cmr (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    intitule VARCHAR(200) NOT NULL,
    description TEXT,
    annee_reference VARCHAR(4),
    valeur_reference VARCHAR(50),
    valeur_cible VARCHAR(50),
    unite VARCHAR(50),
    mode_calcul VARCHAR(50),
    responsable VARCHAR(100),
    latitude VARCHAR(100),
    longitude VARCHAR(100),
    referentiel_id INT,
    resultat_id INT,
    projet_id INT,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des valeurs cibles annuelles du CMR
CREATE TABLE IF NOT EXISTS t_cible_annuelle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    valeur VARCHAR(50),
    annee VARCHAR(4),
    scenario VARCHAR(20),
    cmr_id INT,
    projet_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des valeurs suivies annuelles du CMR
CREATE TABLE IF NOT EXISTS t_suivi_annuelle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    valeur VARCHAR(50),
    annee VARCHAR(4),
    echelle VARCHAR(50),
    classe VARCHAR(50),
    date_suivie DATE,
    observation TEXT,
    scenario VARCHAR(20),
    cmr_id INT,
    projet_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des rapports periodiques
CREATE TABLE IF NOT EXISTS t_rapports_periodiques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50),
    intitule VARCHAR(200),
    periode VARCHAR(50),
    mois_ref VARCHAR(50),
    annee_ref VARCHAR(4),
    description TEXT,
    projet_id INT,
    add_by INT,
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des dashboards
CREATE TABLE IF NOT EXISTS t_requete_fiche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    projet_id INT DEFAULT NULL,
    cmr_id INT DEFAULT NULL,
    query TEXT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des notifications
CREATE TABLE IF NOT EXISTS t_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(50),
    message TEXT,
    type VARCHAR(50) DEFAULT 'default',  -- 'success', 'error', 'warning', 'info'
    entity_type VARCHAR(50), -- 'user', 'group', 'project', 'indicator', 'support'
    entity_id INT,
    is_read TINYINT(1) DEFAULT FALSE,
    is_starred TINYINT(1) DEFAULT FALSE,
    is_archived TINYINT(1) DEFAULT FALSE,
    user_id INT,
    add_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des codes de verification
CREATE TABLE IF NOT EXISTS t_codes_verify (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50),
    email VARCHAR(200),
    state VARCHAR(20) DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des logs de synchronisation
CREATE TABLE IF NOT EXISTS t_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secteur VARCHAR(50),
    entity_name VARCHAR(100),
    entity_id INT,
    operation VARCHAR(10), -- INSERT, UPDATE, DELETE
    sync_status VARCHAR(20) DEFAULT 'pending',
    last_sync DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--##############################################################################
--##############################################################################
CREATE TABLE IF NOT EXISTS t_classeur (
  Code_Classeur int(11) NOT NULL AUTO_INCREMENT,
  Libelle_Classeur text DEFAULT NULL,
  Note_Classeur text DEFAULT NULL,
  Couleur_Classeur text DEFAULT NULL,
  Id_Projet varchar(255) DEFAULT NULL,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (Code_Classeur)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_feuille (
  Code_Feuille bigint(20) NOT NULL AUTO_INCREMENT,
  Code_Classeur int(11) DEFAULT NULL,
  Nom_Feuille text DEFAULT NULL,
  Libelle_Feuille text DEFAULT NULL,
  Nb_Ligne_Impr int(11) DEFAULT NULL,
  Icone text DEFAULT NULL,
  Note text DEFAULT NULL,
  Table_Feuille varchar(255) NOT NULL,
  Structure_Table text DEFAULT NULL,
  Structure_View text NOT NULL,
  Source_Donnees enum('Oui','Non') DEFAULT 'Non',
  Statut int(1) NOT NULL DEFAULT 1,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (Code_Feuille)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_feuille_etrangere (
  Nom_Table varchar(255) NOT NULL,
  Nom_Colonne varchar(255) NOT NULL,
  Valeur varchar(255) NOT NULL,
  PRIMARY KEY (Nom_Table,Nom_Colonne)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_feuille_ligne (
  Code_Feuille_Ligne bigint(20) NOT NULL AUTO_INCREMENT,
  Code_Feuille bigint(20) NOT NULL,
  Nom_Ligne text NOT NULL,
  Libelle_Ligne text NOT NULL,
  Type_Ligne varchar(255) NOT NULL,
  Requis enum('Oui','Non') DEFAULT NULL,
  Afficher enum('Oui','Non') DEFAULT NULL,
  Nom_Collone varchar(255) DEFAULT NULL,
  Rang int(11) NOT NULL DEFAULT 100,
  Mobile enum('Oui','Non') DEFAULT 'Oui',
  Formulaire int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (Code_Feuille_Ligne)
) ENGINE=InnoDB AUTO_INCREMENT=1791 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_feuille_ligne_type (
  Code_Feuille_Ligne_Type int(11) NOT NULL AUTO_INCREMENT,
  Valeur_Feuille_Ligne_Type varchar(255) DEFAULT NULL,
  Libelle_Feuille_Ligne_Type varchar(255) DEFAULT NULL,
  Structure_Feuille_Ligne_Type varchar(255) NOT NULL,
  PRIMARY KEY (Code_Feuille_Ligne_Type)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_feuille_partenaire (
  Code_Feuille varchar(255) NOT NULL,
  code varchar(255) NOT NULL,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (Code_Feuille,code)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_rapport (
  Code_Rapport int(11) NOT NULL AUTO_INCREMENT,
  Code_Classeur int(11) DEFAULT NULL,
  Nom_Rapport varchar(2500) DEFAULT NULL,
  Code_Feuille bigint(20) DEFAULT NULL,
  Group_By varchar(255) DEFAULT NULL,
  Colonne_X varchar(255) DEFAULT NULL,
  Colonne_Y varchar(255) DEFAULT NULL,
  Valeur varchar(255) DEFAULT NULL,
  Feuille_Jointure int(11) DEFAULT NULL,
  Attribut_Jointure_FP varchar(255) DEFAULT NULL,
  Attribut_Jointure_FS varchar(255) DEFAULT NULL,
  Operation varchar(255) DEFAULT NULL,
  Nom_View varchar(2500) DEFAULT NULL,
  Type_Rapport enum('SIMPLE','CROISE','CARTO') DEFAULT NULL,
  Structure_View text DEFAULT NULL,
  Id_Projet varchar(255) DEFAULT NULL,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (Code_Rapport),
  KEY id_feuille (Code_Feuille)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS t_rapport_article (
  Code_Article int(11) NOT NULL AUTO_INCREMENT,
  Code_Rapport int(11) NOT NULL,
  Titre_Article varchar(2500) DEFAULT NULL,
  Description_Article text DEFAULT NULL,
  Photo varchar(2500) DEFAULT NULL,
  Statut enum('Actif','Inactif') DEFAULT 'Actif',
  Validation enum('Oui','Non') DEFAULT 'Non',
  Login varchar(255) NOT NULL,
  Vue int(11) NOT NULL DEFAULT 0,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (Code_Article)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS t_rapport_critere (
  Code_Rapport_Critere bigint(20) NOT NULL AUTO_INCREMENT,
  Code_Rapport int(11) NOT NULL,
  Critere_Colonne varchar(255) DEFAULT NULL,
  Critere_Condition varchar(255) DEFAULT NULL,
  Critere_Valeur varchar(255) DEFAULT NULL,
  Critere_ET_OU enum('ET','OU') DEFAULT NULL,
  PRIMARY KEY (Code_Rapport_Critere)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS t_rapport_indicateur (
  Code_Rapport int(11) NOT NULL AUTO_INCREMENT,
  Code_Classeur int(11) DEFAULT NULL,
  Nom_Rapport varchar(2500) DEFAULT NULL,
  Code_Feuille bigint(20) DEFAULT NULL,
  Group_By varchar(255) DEFAULT NULL,
  Valeur varchar(255) DEFAULT NULL,
  Feuille_Jointure int(11) DEFAULT NULL,
  Attribut_Jointure_FP varchar(255) DEFAULT NULL,
  Attribut_Jointure_FS varchar(255) DEFAULT NULL,
  Operation varchar(255) DEFAULT NULL,
  Nom_View varchar(2500) DEFAULT NULL,
  Structure_View text DEFAULT NULL,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  Id_Projet varchar(255) DEFAULT NULL,
  Affichage varchar(30) DEFAULT NULL,
  Indicateur varchar(11) DEFAULT NULL,
  PRIMARY KEY (Code_Rapport)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS t_requete_carte (
  Code_Rapport int(11) NOT NULL AUTO_INCREMENT,
  Nom_View varchar(2500) DEFAULT NULL,
  Date_Insertion timestamp NOT NULL DEFAULT current_timestamp(),
  Id_Projet varchar(255) NOT NULL,
  intitule text DEFAULT NULL,
  codeSQL text DEFAULT NULL,
  requete_conf int(11) NOT NULL,
  fiche_carto varchar(30) NOT NULL,
  PRIMARY KEY (Code_Rapport)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


--##############################################################################
--##############################################################################
-- Function to encrypt password
DROP FUNCTION IF EXISTS fc_crypter_pass;

CREATE FUNCTION fc_crypter_pass(p_login VARCHAR(100), p_pass VARCHAR(255))
RETURNS VARCHAR(255)
DETERMINISTIC
RETURN (
    SELECT
        IF(id IS NULL OR id = 0, '',
            SHA1(CONCAT(p_pass, id, 'MrvML0012025', id * 2))
        )
    FROM t_users
    WHERE username = p_login
    LIMIT 1
);

-- Function to check login and password
DROP FUNCTION IF EXISTS fc_check_login_mdp;

CREATE FUNCTION fc_check_login_mdp(p_login VARCHAR(100), p_pass VARCHAR(255))
RETURNS CHAR(1)
DETERMINISTIC
RETURN (
    CASE
        WHEN (
            SELECT COUNT(*)
            FROM t_users
            WHERE username = p_login
              AND validity = 1
              AND password = fc_crypter_pass(p_login, p_pass)
        ) = 1
        THEN 'O'
        WHEN (
            SELECT nb_tentatives_echoues
            FROM t_users
            WHERE username = p_login
        ) > 4
        THEN 'B'
        ELSE 'N'
    END
);

--##############################################################################
--##############################################################################
--Procedure to update user passwordDELIMITER $$
-- DELIMITER $$

-- DROP PROCEDURE IF EXISTS pc_maj_pass_user$$

-- CREATE PROCEDURE pc_maj_pass_user(
--     IN p_login VARCHAR(100),
--     IN p_pass  VARCHAR(255)
-- )
-- BEGIN
--     UPDATE t_users
--     SET password = fc_crypter_pass(p_login, p_pass)
--     WHERE username = p_login;
-- END$$

-- DELIMITER ;
