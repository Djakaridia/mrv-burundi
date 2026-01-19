<?php
require_once __DIR__ . '/database.php';
$modelsDir =  __DIR__ . '/../models';
foreach (glob("$modelsDir/*.php") as $modelFile) {
    require_once $modelFile;
}

$database = new Database();
$db = $database->getConnection();

// #####################################################################
// #####################################################################
// Méthode pour créer les tables à partir du fichier SQL
function createAllTables(PDO $db)
{
    if ($db === null) {
        throw new RuntimeException('Database connection failed');
    }

    $sqlFilePath = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFilePath)) {
        throw new RuntimeException("SQL file not found");
    }

    $sql = file_get_contents($sqlFilePath);
    if ($sql === false) {
        throw new RuntimeException("Failed to read SQL file");
    }

    // Supprimer commentaires --
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);

    // Découpage SIMPLE (schema.sql = TABLES UNIQUEMENT)
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $query) {
        if ($query !== '') {
            $db->exec($query);
        }
    }

    echo "<script>console.log('Tables created successfully');</script>";
}

function createSingleTable(PDO $db, string $sqltable): bool
{
    if (trim($sqltable) === '') {
        return false;
    }

    try {
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $db->exec($sqltable);
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");

        return true;
    } catch (Throwable $e) {
        error_log('Create table error: ' . $e->getMessage());
        return false;
    }
}

function addRootRoles(PDO $db)
{
    if ($db === null) {
        echo "<script>console.log('La connexion à la base de données a échoué.');</script>";
        return;
    }

    $role = new Role($db);
    $role->name = 'Admin';
    $role->niveau = 1;
    $role->page_edit = 'admin';
    $role->page_delete = 'admin';
    $role->page_interdite = 'admin';
    $role->description = 'Role administrateur';
    $role->add_by = 1;

    $sql = "SELECT * FROM t_roles WHERE name = 'Admin'";
    $stmt = $db->query($sql);
    if ($stmt->rowCount() == 0) {
        try {
            $role->create();
        } catch (PDOException $exception) {
            echo "<script>console.log('stm-rol: [false]')</script>";
        }
    }
    $db = null;
}

function addRootAdmin(PDO $db)
{
    if ($db === null) {
        echo "<script>console.log('La connexion à la base de données a échoué.');</script>";
        return;
    }

    $user = new User($db);
    $user->nom = 'Admin';
    $user->prenom = 'Sys';
    $user->username = 'admin';
    $user->email = 'admin.sys@mrv.com';
    $user->phone = '00000000';
    $user->password = 'mrv_bur@1234';
    $user->fonction = 'admin';
    $user->role_id = 1;
    $user->structure_id = 1;

    $sql = "SELECT * FROM t_users WHERE username = 'admin'";
    $stmt = $db->query($sql);
    if ($stmt->rowCount() == 0) {
        try {
            $user->create();
        } catch (\Throwable $th) {
            echo "<script>console.log('stm-usr: [false]')</script>";
        }
    }
    $db = null;
}

function addRootStructure(PDO $db)
{
    if ($db === null) {
        echo "<script>console.log('La connexion à la base de données a échoué.');</script>";
        return;
    }

    $structure = new Structure($db);
    $structure->code = '01';
    $structure->sigle = 'UGP';
    $structure->logo = '';
    $structure->email = 'ugp-pro@mrv.com';
    $structure->phone = '00 00 00 00';
    $structure->address = 'Adresse locale';
    $structure->description = 'Unité de Gestion du Projet';
    $structure->add_by = 1;
    $structure->type_id = 1;

    $sql = "SELECT * FROM t_structures WHERE email = 'ugp-pro@mrv.com'";
    $stmt = $db->query($sql);
    if ($stmt->rowCount() == 0) {
        try {
            $structure->create();
        } catch (PDOException $exception) {
            echo "<script>console.log('stm-struct: [false]')</script>";
        }
    }
    $db = null;
}

function addRootTypeStructure(PDO $db)
{
    if ($db === null) {
        echo "<script>console.log('La connexion à la base de données a échoué.');</script>";
        return;
    }

    $dataList = [
        ['name' => 'Partenaire de mise en œuvre', 'description' => 'Partenaire de mise en œuvre', 'add_by' => 1],
        ['name' => 'Partenaire stratégique', 'description' => 'Partenaire stratégique', 'add_by' => 1],
        ['name' => 'Partenaire de financement', 'description' => 'Partenaire de financement (Bailleurs)', 'add_by' => 1],
    ];

    $type_structure = new StructureType($db);
    foreach ($dataList as $data) {
        $type_structure->name = $data['name'];
        $type_structure->description = $data['description'];
        $type_structure->add_by = $data['add_by'];

        $sql = "SELECT * FROM t_type_structures WHERE name = '{$data["name"]}'";
        $stmt = $db->query($sql);
        if ($stmt->rowCount() == 0) {
            try {
                $type_structure->create();
            } catch (PDOException $exception) {
                echo "<script>console.log('stm-type-struct: [false]')</script>";
            }
        }
    }
    $db = null;
}

function addRootSecteur(PDO $db)
{
    if ($db === null) {
        echo "<script>console.log('La connexion à la base de données a échoué.');</script>";
        return;
    }

    $dataList = [
        ['code' => '01', 'name' => 'Agriculture', 'description' => 'Description du secteur Agriculture', 'add_by' => 1],
        ['code' => '02', 'name' => 'Energie', 'description' => 'Description du secteur Energie', 'add_by' => 1],
        ['code' => '03', 'name' => 'PUIP', 'description' => 'Description du secteur PUIP', 'add_by' => 1],
        ['code' => '04', 'name' => 'Déchets', 'description' => 'Description du secteur Déchets', 'add_by' => 1],
    ];

    $secteur = new Secteur($db);
    foreach ($dataList as $data) {
        $secteur->code = $data['code'];
        $secteur->name = $data['name'];
        $secteur->description = $data['description'];
        $secteur->add_by = $data['add_by'];

        $sql = "SELECT * FROM t_secteurs WHERE name = '{$data['name']}'";
        $stmt = $db->query($sql);
        if ($stmt->rowCount() == 0) {
            try {
                $secteur->create();
            } catch (PDOException $exception) {
                echo "<script>console.log('stm-secteur: [false]')</script>";
            }
        }
    }
    $db = null;
}

function addRootDossier(PDO $db)
{
    if ($db === null) {
        echo "<script>console.log('La connexion à la base de données a échoué.');</script>";
        return;
    }

    $dataList = [
        ['name' => 'Acteurs', 'description' => 'Dossier pour les acteurs', 'type' => 'acteur', 'add_by' => 1],
        ['name' => 'Programmes', 'description' => 'Dossier pour les programmes', 'type' => 'programme', 'add_by' => 1],
        ['name' => 'Projets', 'description' => 'Dossier pour les projets', 'type' => 'projet', 'add_by' => 1],
        ['name' => 'Indicateurs', 'description' => 'Dossier pour les indicateurs', 'type' => 'indicateur', 'add_by' => 1],
        ['name' => 'Groupe de travail', 'description' => 'Dossier pour les groupes de travail', 'type' => 'groups', 'add_by' => 1],
        ['name' => 'Réunions', 'description' => 'Dossier pour les réunions', 'type' => 'reunion', 'add_by' => 1],
    ];

    $dossier = new Dossier($db);
    foreach ($dataList as $data) {
        $dossier->name = $data['name'];
        $dossier->description = $data['description'];
        $dossier->type = $data['type'];
        $dossier->parent = 0;
        $dossier->add_by = $data['add_by'];

        $sql = "SELECT * FROM t_dossiers WHERE name = '{$data['name']}'";
        $stmt = $db->query($sql);
        if ($stmt->rowCount() == 0) {
            try {
                $dossier->create();
            } catch (PDOException $exception) {
                echo "<script>console.log('stm-dossier: [false]')</script>";
            }
        }
    }
    $db = null;
}

function addRootNiveaux(PDO $db)
{
    if ($db === null) {
        error_log('Database connection is null in addRootNiveaux');
        return;
    }

    $dataList = [
        ['name' => 'Objectif de développement', 'type' => 'impact', 'level' => '0', 'programme' => 0, 'add_by' => 1],
        ['name' => 'Résultats intermédiaire', 'type' => 'effet', 'level' => '1', 'programme' => 0, 'add_by' => 1],
        ['name' => 'Résultats finaux', 'type' => 'effet', 'level' => '2', 'programme' => 0, 'add_by' => 1],
    ];

    $niveau = new Niveau($db);
    foreach ($dataList as $data) {
        $checkSql = "SELECT COUNT(*) FROM t_niveau WHERE name = :name";
        $stmt = $db->prepare($checkSql);
        $stmt->execute([':name' => $data['name']]);
        $exists = $stmt->fetchColumn();

        if (!$exists) {
            $niveau->name = $data['name'];
            $niveau->type = $data['type'];
            $niveau->level = $data['level'];
            $niveau->programme = $data['programme'];
            $niveau->add_by = $data['add_by'];

            try {
                $niveau->create();
            } catch (PDOException $e) {
                echo "<script>console.log('stm-niveau: [false]')</script>";
            }
        }
    }
    $db = null;
}

function addRootFeuilleLigneType(PDO $db)
{
    if ($db === null) {
        error_log('Database connection is null in addFeuilleType');
        return false;
    }

    try {
        $sql = "
            INSERT INTO t_feuille_ligne_type 
            (Code_Feuille_Ligne_Type, Valeur_Feuille_Ligne_Type, Libelle_Feuille_Ligne_Type, Structure_Feuille_Ligne_Type) 
            VALUES
            (1, 'TEXT', 'TEXT', ''),
            (2, 'INT', 'ENTIER', ''),
            (3, 'DOUBLE', 'DOUBLE', ''),
            (4, 'DATE', 'DATE', ''),
            (5, 'CHOIX', 'CHOIX', ''),
            (6, 'COULEUR', 'COULEUR', ''),
            (7, 'FICHIER', 'FICHIER', ''),
            (8, 'FEUILLE', 'FEUILLE', ''),
            (9, 'RAPPORT', 'RAPPORT', ''),
            (10, 'SOMME', 'SOMME', ''),
            (11, 'DIFFERENCE', 'DIFFERENCE', ''),
            (12, 'PRODUIT', 'PRODUIT', ''),
            (13, 'MOYENNE', 'MOYENNE', ''),
            (14, 'COMPTER', 'COMPTER', ''),
            (15, 'QRCODE', 'QRCODE', ''),
            (16, 'CHOIX MULTIPLES', 'CHOIX MULTIPLES', ''),
            (17, 'SIGNATURE', 'SIGNATURE', '')
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        error_log('Error in addFeuilleType: ' . $e->getMessage());
        return false;
    }
}

// #####################################################################
// #####################################################################
// Fonction de gestion de fichiers
function downloadFile($db, $file_id)
{
    $document = new Documents($db);
    if ($file_id) {
        $document->id = $file_id;
        $download_file = $document->readById();

        if ($download_file) {
            $file_name = $download_file['name'];
            $file_path = $download_file['file_path'];

            if (file_exists($file_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));

                ob_clean();
                flush();
                readfile($file_path);
                exit;
            }
        }
    }
}

function uploadFile($file, $allow_file, $uploadDirectory)
{
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $fileDestination = $uploadDirectory . $newFileName;

    // Vérification de l'extension
    if (!empty($fileExt) && !in_array('.' . $fileExt, explode(', ', $allow_file))) {
        http_response_code(400);
        echo json_encode(array('status' => 'danger', 'message' => 'Type de fichier non autorisé. Veuillez choisir un fichier de type: ' . $allow_file));
        exit();
    }

    // Vérification et déplacement du fichier
    if ($fileError === 0) {
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            return $fileDestination;
        }
    }
}

function deleteFile($path)
{
    $filePath = $path;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// #####################################################################
// #####################################################################
// Fonction de vérification des permissions
function checkPermis($db, $action, $level = 3)
{
    if (!isset($_SESSION['user-data']['user-id'])) {
        return false;
    }

    $user = new User($db);
    $user->id = $_SESSION['user-data']['user-id'];
    $user_data = $user->readById();
    if (!$user_data || !isset($user_data['role_id'])) {
        return false;
    }

    $role = new Role($db);
    $role->id = $user_data['role_id'];
    $role_data = $role->readById();
    if (!$role_data) {
        return false;
    }

    if ($role_data['niveau'] == 1) {
        return true;
    }

    if ($role_data['niveau'] > $level) {
        return false;
    }

    $page_curr = basename($_SERVER['PHP_SELF']);
    $edit_permission = isset($role_data['page_edit']) ? explode('|', $role_data['page_edit']) : [];
    $delete_permission = isset($role_data['page_delete']) ? explode('|', $role_data['page_delete']) : [];

    switch ($action) {
        case 'update':
            return in_array($page_curr, $edit_permission);
        case 'delete':
            return in_array($page_curr, $delete_permission);
        default:
            return false;
    }
}

// #####################################################################
// #####################################################################
// Fonctions d'affichage des messages
function showMessage($message, $type = 'success')
{
    $alertType = match ($type) {
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        default => 'alert-info',
    };

    echo "<div class='alert rounded-0 p-3 text-center alert-dismissible fade show $alertType' role='alert'>$message</div>";
}

function getNotifyIcon($type)
{
    $icons = [
        'success' => 'fas fa-check-circle bg-success-subtle text-success',
        'error' => 'fas fa-exclamation-circle bg-danger-subtle text-danger',
        'warning' => 'fas fa-exclamation-triangle bg-warning-subtle text-warning',
        'info' => 'fas fa-info-circle bg-info-subtle text-info',
        'default' => 'fas fa-bell bg-primary-subtle text-primary',
    ];
    return $icons[$type] ?? 'fas fa-bell bg-primary-subtle text-primary';
}

function getBadgeClass($status)
{
    return match (strtolower($status)) {
        'planifiée' => 'info',
        'en cours' => 'warning',
        'en attente' => 'light',
        'terminée' => 'success',
        'annulée' => 'danger',
        default => 'secondary',
    };
}

function getBadgeFaIcon($status)
{
    return match (strtolower($status)) {
        'planifiée' => 'fa-calendar',
        'en cours' => 'fa-calendar-minus',
        'en attente' => 'fa-clock',
        'terminée' => 'fa-check',
        'annulée' => 'fa-times',
        default => 'fa-info',
    };
}

// #####################################################################
// #####################################################################
// Fonctions de chargement des variables d'environnement
function loadVarEnv()
{
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        die('Fichier .env manquant.');
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = trim($value);
    }
}

// #####################################################################
// #####################################################################
// Fonctions de listes 
function listTypeAction()
{
    return [
        'attenuation' => 'Atténuation',
        'adaptation' => 'Adaptation'
    ];
}

function listTypeScenario(){
    return [
        'conditionnel' => 'Conditionnel',
        'inconditionnel' => 'Inconditionnel'
    ];
}

function listModeCalcul()
{
    return [
        'count' => 'Nombre',    // Nombre : nombre de valeurs
        'sum' => 'Somme',       // Somme : somme des valeurs
        'avg' => 'Moyenne',     // Moyenne : somme des valeurs / nombre de valeurs
        'ratio' => 'Ratio',     // Ratio : dernière / avant-dernière valeur
        'default' => 'N/A'      // N/A : non applicable
    ];
}

function listModeAggregation()
{
    return [
        'sum'    => 'Somme',        // Somme des valeurs
        'avg'    => 'Moyenne',      // Moyenne des valeurs
        'ratio'  => 'Ratio',        // Ratio : dernière / avant-dernière valeur
        'max'    => 'Maximum',      // Valeur la plus élevée
        'min'    => 'Minimum',      // Valeur la plus basse
        'report' => 'Rapport',      // Dernière valeur
    ];
}

function listPeriodicite()
{
    return [
        '1' => 'Mensuelle',
        '3' => 'Trimestrielle',
        '6' => 'Semestrielle',
        '12' => 'Annuelle',
    ];
}

function listMois()
{
    return [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre'
    ];
}

function listCouleur()
{
    return [
        'Primaire' => 'primary',
        'Secondaire' => 'secondary',
        'Succès' => 'success',
        'Danger' => 'danger',
        'Avertissement' => 'warning',
        'Information' => 'info',
        'Clair' => 'light',
        'Sombre' => 'dark',
    ];
}

function listModeleTypologie()
{
    return [
        'valeur_absolue' => 'Valeur absolue',
        'valeur_relative' => 'Valeur relative',
        'typo_quantitative' => 'Typologie quantitative',
        'typo_qualitative' => 'Typologie qualitative',
    ];
}

function listIcones()
{
    $icones = [
        // Icônes thématiques
        'Population' => ['icon' => 'fa fa-users', 'unicode' => '&#xf0c0;'],
        'Energie' => ['icon' => 'fa fa-bolt', 'unicode' => '&#xf0e7;'],
        'Eau' => ['icon' => 'fa fa-tint', 'unicode' => '&#xf043;'],
        'Recyclage' => ['icon' => 'fa fa-recycle', 'unicode' => '&#xf1b8;'],
        'Nature' => ['icon' => 'fa fa-leaf', 'unicode' => '&#xf06c;'],
        'Climat' => ['icon' => 'fa fa-cloud', 'unicode' => '&#xf0c2;'],
        'Industrie' => ['icon' => 'fa fa-industry', 'unicode' => '&#xf275;'],
        'Transport' => ['icon' => 'fa fa-car', 'unicode' => '&#xf1b9;'],
        'Santé' => ['icon' => 'fa fa-heart', 'unicode' => '&#xf004;'],
        'Education' => ['icon' => 'fa fa-graduation-cap', 'unicode' => '&#xf19d;'],

        // Icônes de base données
        'Données' => ['icon' => 'fa fa-database', 'unicode' => '&#xf1c0;'],
        'Collecte données' => ['icon' => 'fa fa-table', 'unicode' => '&#xf0ce;'],
        'Stockage données' => ['icon' => 'fa fa-hdd-o', 'unicode' => '&#xf0a0;'],
        'Données brutes' => ['icon' => 'fa fa-file-code-o', 'unicode' => '&#xf1c9;'],
        'Graphique circulaire' => ['icon' => 'fa fa-pie-chart', 'unicode' => '&#xf200;'],
        'Calcul' => ['icon' => 'fa fa-calculator', 'unicode' => '&#xf1ec;'],
        'Pourcentage' => ['icon' => 'fa fa-percent', 'unicode' => '&#xf295;'],

        // Icônes statistiques et analyse
        'Statistiques' => ['icon' => 'fa fa-bar-chart', 'unicode' => '&#xf080;'],
        'Analyse' => ['icon' => 'fa fa-line-chart', 'unicode' => '&#xf201;'],
        'Graphique' => ['icon' => 'fa fa-area-chart', 'unicode' => '&#xf1fe;'],
        'Indicateurs' => ['icon' => 'fa fa-tachometer', 'unicode' => '&#xf0e4;'],
        'Filtre' => ['icon' => 'fa fa-filter', 'unicode' => '&#xf0b0;'],
        'Tri ascendant' => ['icon' => 'fa fa-sort-amount-up', 'unicode' => '&#xf160;'],
        'Tri descendant' => ['icon' => 'fa fa-sort-amount-down', 'unicode' => '&#xf161;'],
        'Export' => ['icon' => 'fa fa-download', 'unicode' => '&#xf019;'],
        'Import' => ['icon' => 'fa fa-upload', 'unicode' => '&#xf093;'],
        'Recherche' => ['icon' => 'fa fa-search', 'unicode' => '&#xf002;'],
        'Carte' => ['icon' => 'fa fa-map-marker', 'unicode' => '&#xf041;'],
        'Temps' => ['icon' => 'fa fa-clock-o', 'unicode' => '&#xf017;'],
        'Calendrier' => ['icon' => 'fa fa-calendar', 'unicode' => '&#xf073;'],
        'Comparaison' => ['icon' => 'fa fa-balance-scale', 'unicode' => '&#xf24e;'],

        // MRV spécifique
        'Monitoring' => ['icon' => 'fa fa-eye', 'unicode' => '&#xf06e;'],
        'Reporting' => ['icon' => 'fa fa-file-text', 'unicode' => '&#xf15c;'],
        'Vérification' => ['icon' => 'fa fa-check-circle', 'unicode' => '&#xf058;'],
        'Audit' => ['icon' => 'fa fa-search-plus', 'unicode' => '&#xf00e;'],
        'Contrôle qualité' => ['icon' => 'fa fa-check-square', 'unicode' => '&#xf14a;'],
        'Conformité' => ['icon' => 'fa fa-gavel', 'unicode' => '&#xf0e3;'],

        // Flux de données
        'Entrée données' => ['icon' => 'fa fa-sign-in', 'unicode' => '&#xf090;'],
        'Sortie données' => ['icon' => 'fa fa-sign-out', 'unicode' => '&#xf08b;'],
        'Transfert données' => ['icon' => 'fa fa-exchange', 'unicode' => '&#xf0ec;'],
        'Synchronisation' => ['icon' => 'fa fa-refresh', 'unicode' => '&#xf021;'],
        'API' => ['icon' => 'fa fa-plug', 'unicode' => '&#xf1e6;'],

        // Sécurité et accès
        'Sécurité données' => ['icon' => 'fa fa-lock', 'unicode' => '&#xf023;'],
        'Accès données' => ['icon' => 'fa fa-key', 'unicode' => '&#xf084;'],
        'Partage données' => ['icon' => 'fa fa-share-alt', 'unicode' => '&#xf1e0;'],
        'Permission' => ['icon' => 'fa fa-user-secret', 'unicode' => '&#xf21b;'],

        // Documents et rapports
        'Documentation' => ['icon' => 'fa fa-book', 'unicode' => '&#xf02d;'],
        'Archive' => ['icon' => 'fa fa-archive', 'unicode' => '&#xf187;'],
        'Export PDF' => ['icon' => 'fa fa-file-pdf-o', 'unicode' => '&#xf1c1;'],
        'Export Excel' => ['icon' => 'fa fa-file-excel-o', 'unicode' => '&#xf1c3;'],

        // Alertes et notifications
        'Alerte' => ['icon' => 'fa fa-bell', 'unicode' => '&#xf0f3;'],
        'Notification' => ['icon' => 'fa fa-bell-o', 'unicode' => '&#xf0a2;'],
        'Seuil critique' => ['icon' => 'fa fa-exclamation-triangle', 'unicode' => '&#xf071;']
    ];

    ksort($icones);
    return $icones;
}

// #####################################################################
// #####################################################################
// #####################################################################
function cleanColumnName($header)
{
    $header = str_replace(
        ['₀', '₁', '₂', '₃', '₄', '₅', '₆', '₇', '₈', '₉', '⁰', '¹', '²', '³', '⁴', '⁵', '⁶', '⁷', '⁸', '⁹'],
        ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        $header
    );
    $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header);
    $header = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($header));
    $header = preg_replace('/_+/', '_', $header);
    $header = trim($header, '_');
    return $header;
}

function normalizeNumber($value, $precision = 3)
{
    if ($value === null || $value === '') return null;
    $value = str_replace(',', '.', $value);
    return round((float)$value, $precision);
}


function formatCellValue($value)
{
    if ($value === null || $value === '') return null;

    if (is_numeric($value)) {
        if (is_float($value) || is_double($value)) {
            $formatted = rtrim(sprintf('%.6f', $value), '0');
            $formatted = rtrim($formatted, '.');

            if (strpos($formatted, '.') !== false) {
                $parts = explode('.', $formatted);
                if (strlen($parts[1]) > 3) $formatted = number_format($value, 3, '.', '');
            }
            return $formatted;
        }
        return $value;
    }

    $stringValue = trim($value);
    if (preg_match('/^-?\d+[\.,]\d+$/', $stringValue)) {
        $numericValue = str_replace(',', '.', $stringValue);
        $floatValue = (float)$numericValue;
        return number_format($floatValue, 3, '.', '');
    }

    if (is_numeric($stringValue)) return (int)$stringValue;
    return $stringValue;
}

function removeAccents($string)
{
    if ($string === null) {
        return "";
    }

    $string = (string) $string;
    $transliteration_map = [
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'Ç' => 'C',
        'ç' => 'c',
        'Ñ' => 'N',
        'ñ' => 'n'
    ];

    return strtr($string, $transliteration_map);
}


// #####################################################################
// #####################################################################
// Fonctions de calcul des indicateurs
function calculSuiviData(array $suivis, string $modeCalcul)
{
    try {
        if (empty($suivis)) {
            return '<span class="text-muted">-</span>';
        }

        $values = array_column($suivis, 'valeur');
        $numericValues = array_filter($values, 'is_numeric');
        $numericValues = array_map('floatval', $numericValues);
        $count = count($numericValues);

        if (empty($numericValues)) {
            return '<span class="text-warning">N/A</span>';
        }

        $result = null;

        switch ($modeCalcul) {
            case 'count':
                $result = $count;
                break;

            case 'sum':
                $result = array_sum($numericValues);
                break;

            case 'avg':
                $result = $count > 0 ? array_sum($numericValues) / $count : 0;
                break;

            case 'ratio':
                if ($count >= 2 && $numericValues[$count - 2] != 0) {
                    $result = $numericValues[$count - 1] / $numericValues[$count - 2];
                } else {
                    return '<span class="text-warning">N/A</span>';
                }
                break;

            default:
                return '<span class="text-warning">N/A</span>';
        }

        return number_format($result, 2);
    } catch (Exception $e) {
        error_log("Erreur dans calculSuiviData: " . $e->getMessage());
        return '<span class="text-danger">Err</span>';
    }
}

function calculAggregationData(array $suivis, string $modeAggregation)
{
    try {
        if (empty($suivis)) {
            return '<span class="text-muted">-</span>';
        }

        $values = array_column($suivis, 'valeur');
        $numericValues = array_filter($values, 'is_numeric');
        $numericValues = array_map('floatval', $numericValues);

        if (empty($numericValues)) {
            return '<span class="text-warning">N/A</span>';
        }

        $count = count($numericValues);
        $result = null;

        switch ($modeAggregation) {
            case 'sum':
                $result = array_sum($numericValues);
                break;

            case 'avg':
                $result = array_sum($numericValues) / $count;
                break;

            case 'ratio':
                if ($count >= 2 && $numericValues[$count - 2] != 0) {
                    $result = $numericValues[$count - 1] / $numericValues[$count - 2];
                } else {
                    return '<span class="text-warning">N/A</span>';
                }
                break;

            case 'max':
                $result = max($numericValues);
                break;

            case 'min':
                $result = min($numericValues);
                break;

            case 'report':
                $result = $numericValues[$count - 1];
                break;

            default:
                return '<span class="text-warning">Err</span>';
        }

        return number_format($result, 2);
    } catch (Exception $e) {
        error_log("Erreur dans calculAggregationData: " . $e->getMessage());
        return '<span class="text-danger">Err</span>';
    }
}

function requeteFiche($table, $colValue, $colGroup, $operator, $et_ou_criteres, $champ_criteres, $condition_criteres, $valeur_criteres)
{
    if ($operator === "count") $select = "COUNT($colValue) AS result";
    else $select = strtoupper($operator) . "($colValue) AS result";

    $sql = "SELECT $colGroup, $select FROM $table";
    $whereClauses = [];
    $params = [];

    if (!empty($champ_criteres)) {
        for ($i = 0; $i < count($champ_criteres); $i++) {
            $champ = $champ_criteres[$i];
            $cond  = $condition_criteres[$i];
            $val   = $valeur_criteres[$i];
            $et_ou = $et_ou_criteres[$i];

            switch ($cond) {
                case "%x%":
                    $wherePart = "$champ LIKE ?";
                    $params[] = "%$val%";
                    break;

                case "x%":
                    $wherePart = "$champ LIKE ?";
                    $params[] = "$val%";
                    break;

                case "%x":
                    $wherePart = "$champ LIKE ?";
                    $params[] = "%$val";
                    break;

                default:
                    $wherePart = "$champ $cond ?";
                    $params[] = $val;
            }

            $whereClauses[] = [
                "logic" => ($i == 0 ? "" : $et_ou),
                "sql"   => $wherePart
            ];
        }
    }

    if ($whereClauses) {
        $sql .= " WHERE ";
        foreach ($whereClauses as $wc) {
            $sql .= ($wc['logic'] ? " {$wc['logic']} " : "") . $wc['sql'];
        }
    }

    $sql .= " GROUP BY $colGroup";

    return $sql;
}

// #####################################################################
// #####################################################################
// Fonction pour rendre le code js incomprehensible
function minify_js($code)
{
    $code = preg_replace('#/\*.*?\*/#s', '', $code);
    $code = preg_replace('#//.*#', '', $code);
    $code = preg_replace('/\s+/', ' ', $code);
    return trim($code);
}

// $src = file_get_contents('./assets/scripts/func-action.js');
// file_put_contents('./public/js/func-action.min.js', minify_js($src));


// #####################################################################
// #####################################################################
function getClientIp(): string
{
    $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ipList = explode(',', $_SERVER[$k]);
            foreach ($ipList as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
            return trim($ipList[0]);
        }
    }
    return 'unknown';
}

function parseUserAgent(string $ua): array
{
    $uaLower = strtolower($ua);
    $browser = 'Inconnu';
    $browserVersion = '';
    $os = 'Inconnu';
    $device = 'Desktop';

    if (preg_match('#(edge|edg|edgios|edga)/? ?([0-9\.]+)#i', $ua, $m)) {
        $browser = 'Edge';
        $browserVersion = $m[2];
    } elseif (preg_match('#opr\/([0-9\.]+)#i', $ua, $m) || preg_match('#opera\/([0-9\.]+)#i', $ua, $m)) {
        $browser = 'Opera';
        $browserVersion = $m[1] ?? $m[2] ?? '';
    } elseif (preg_match('#(chrome|crios)\/([0-9\.]+)#i', $ua, $m)) {
        $browser = 'Chrome';
        $browserVersion = $m[2];
    } elseif (preg_match('#version\/([0-9\.]+).*safari#i', $ua, $m) && stripos($ua, 'chrome') === false) {
        $browser = 'Safari';
        $browserVersion = $m[1];
    } elseif (preg_match('#firefox\/([0-9\.]+)#i', $ua, $m)) {
        $browser = 'Firefox';
        $browserVersion = $m[1];
    } elseif (preg_match('#msie\s([0-9\.]+)#i', $ua, $m) || preg_match('#rv:([0-9\.]+)\) like gecko#i', $ua, $m)) {
        $browser = 'Internet Explorer';
        $browserVersion = $m[1];
    }

    if (preg_match('#windows nt 10#i', $ua)) {
        $os = 'Windows 10';
    } elseif (preg_match('#windows nt 6\.3#i', $ua)) {
        $os = 'Windows 8.1';
    } elseif (preg_match('#windows nt 6\.2#i', $ua)) {
        $os = 'Windows 8';
    } elseif (preg_match('#windows nt 6\.1#i', $ua)) {
        $os = 'Windows 7';
    } elseif (preg_match('#windows nt 6\.0#i', $ua)) {
        $os = 'Windows Vista';
    } elseif (preg_match('#windows nt 5\.1|xp#i', $ua)) {
        $os = 'Windows XP';
    } elseif (preg_match('#macintosh|mac os x#i', $ua)) {
        if (preg_match('#mac os x ([0-9_\.]+)#i', $ua, $m)) {
            $os = 'macOS ' . str_replace('_', '.', $m[1]);
        } else {
            $os = 'macOS';
        }
    } elseif (preg_match('#android ([0-9\.]+)#i', $ua, $m)) {
        $os = 'Android ' . $m[1];
    } elseif (preg_match('#android#i', $ua)) {
        $os = 'Android';
    } elseif (preg_match('#iphone os ([0-9_]+)#i', $ua, $m)) {
        $os = 'iOS ' . str_replace('_', '.', $m[1]);
    } elseif (preg_match('#ipad#i', $ua)) {
        $os = 'iPadOS';
    } elseif (preg_match('#linux#i', $ua)) {
        $os = 'Linux';
    }

    if (preg_match('#mobile|iphone|ipod|android.*mobile|windows phone#i', $ua)) {
        $device = 'Mobile';
    } elseif (preg_match('#ipad|tablet|android(?!.*mobile)#i', $ua)) {
        $device = 'Tablet';
    } else {
        $device = 'Desktop';
    }

    $browserFull = $browser . ($browserVersion ? ' ' . $browserVersion : '');
    return ['browser' => $browserFull, 'os' => $os, 'device' => $device, 'ua_raw' => $ua];
}
