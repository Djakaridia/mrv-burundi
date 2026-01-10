<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Configuration Initiale de la Base de Données | MRV - Burundi</title>

    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/favicon/favicon.png" />
    <link rel="manifest" href="assets/favicon/manifest.json" />
    <meta name="theme-color" content="#ffffff" />

    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

    <link href="assets/css/theme-rtl.css" type="text/css" rel="stylesheet" id="style-rtl" />
    <link href="assets/css/theme.min.css" type="text/css" rel="stylesheet" id="style-default">
    <link href="assets/css/user-rtl.min.css" type="text/css" rel="stylesheet" id="user-style-rtl">
    <link href="assets/css/user.min.css" type="text/css" rel="stylesheet" id="user-style-default">
    <?php
    require_once './config/functions.php';
    loadVarEnv();

    // Vérification du ticket
    $ticket = $_GET['tk'] ?? '';
    $md5_ticket = md5($ticket);
    if ($md5_ticket !== $_ENV['TICKET_PAGE']) {
        http_response_code(403);
        header('Location: 403.php');
        die();
    }

    // Connection a la base de données
    $database = new Database();
    $db = $database->getConnection();

    // Recuperation du schema.sql
    $schemaFilePath = __DIR__ . '/config/schema.sql';
    $schema = file_get_contents($schemaFilePath);
    $tables = explode('CREATE TABLE IF NOT EXISTS ', $schema);
    $tables = array_filter($tables, function ($table) {
        return strpos($table, 't_') === 0;
    });

    $tablesSchema = [];
    foreach ($tables as $table) {
        $preStruc = explode(');', $table)[0] . ');';
        $tableName = explode(' ', $preStruc)[0];
        $tableStruc = 'CREATE TABLE IF NOT EXISTS ' . $preStruc;
        $tablesSchema[$tableName] = $tableStruc;
    }

    ksort($tablesSchema);

    // Traitement des actions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $action = $_POST['action'] ?? '';
            $table = $_POST['table'] ?? '';
            switch ($action) {
                case 'all':
                    createAllTables($db);
                    addRootRoles($db);
                    addRootTypeStructure($db);
                    addRootStructure($db);
                    addRootAdmin($db);
                    addRootNiveaux($db);
                    addRootSecteur($db);
                    addRootDossier($db);
                    addRootFeuilleLigneType($db);
                    showMessage("Configuration complète terminée avec succès !", 'success');
                    break;
                case 'create_all_tables':
                    createAllTables($db);
                    showMessage("Tables créées avec succès.", 'success');
                    break;
                case 'create_single_table':
                    createSingleTable($db, $tablesSchema[$table]);
                    showMessage("Table créée avec succès.", 'success');
                    break;
                case 'add_roles':
                    addRootRoles($db);
                    showMessage("Rôles ajoutés avec succès.", 'success');
                    break;
                case 'add_admin':
                    addRootAdmin($db);
                    showMessage("Administrateur ajouté avec succès.", 'success');
                    break;
                case 'add_structure':
                    addRootStructure($db);
                    showMessage("Acteur ajouté avec succès.", 'success');
                    break;
                case 'add_type_structure':
                    addRootTypeStructure($db);
                    showMessage("Types de acteurs ajoutés avec succès.", 'success');
                    break;
                case 'add_secteur':
                    addRootSecteur($db);
                    showMessage("Secteurs ajoutés avec succès.", 'success');
                    break;
                case 'add_dossiers':
                    addRootDossier($db);
                    showMessage("Dossiers ajoutés avec succès.", 'success');
                    break;
                case 'add_niveaux':
                    addRootNiveaux($db);
                    showMessage("Niveaux ajoutés avec succès.", 'success');
                    break;
                case 'add_feuille_ligne_type':
                    addRootFeuilleLigneType($db);
                    showMessage("Feuille Ligne Type ajoutés avec succès.", 'success');
                    break;
                default:
                    showMessage("Action non reconnue.", 'warning');
            }
        } catch (Exception $e) {
            showMessage("Erreur: " . $e->getMessage(), 'error');
        }
    }
    ?>
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row justify-content-center" style="height: 90vh;">
            <div class="col-lg-5 col-md-6 col-sm-12 py-5">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-center p-3">
                        <h4 class="mb-0 text-white">Configuration des tables de la Base de Données</h4>
                    </div>
                    <div class="card-body">
                        <!-- Migration de table specifique -->
                        <form class="card p-3 rounded-1 shadow mb-5" action="" method="POST">
                            <div class="form-floating form-floating-advance-select mb-3">
                                <label for="floaTingLabelSingleSelect">Migration d'une Table spécifique (<?php echo count($tablesSchema) ?>)</label>
                                <select name="table" id="table" class="form-select" data-choices="data-choices" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                    <option value="" selected disabled>Choisir une Table</option>
                                    <?php foreach ($tablesSchema as $tableName => $tableStruc) { ?>
                                        <option value="<?php echo $tableName; ?>"><?php echo strtoupper(str_replace('_', ' ', explode('t_', $tableName)[1])); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button title="Migrer la Table" type="submit" name="action" value="create_single_table" class="btn btn-subtle-info">
                                Migrer la Table
                            </button>
                        </form>

                        <!-- Migration des toutes les tables -->
                        <form class="card p-3 rounded-1 shadow mb-5" action="" method="POST">
                            <button title="Migrer toutes les Tables" type="submit" name="action" value="create_all_tables" class="btn btn-subtle-primary">
                                Migrer toutes les Tables
                            </button>
                        </form>

                        <!-- Configuration de toutes les tables -->
                        <form class="card p-3 rounded-1 shadow mb-5" method="POST">
                            <button title="Tout Configurer (Full Setup)" type="submit" name="action" value="all" class="btn btn-primary">
                                Tout Configurer (Full Setup)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-md-6 col-sm-12 py-5">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-center p-3">
                        <h4 class="mb-0 text-white">Importation des données initiales</h4>
                    </div>
                    <div class="card-body">
                        <form class="d-grid gap-3" action="" method="POST">
                            <button type="submit" name="action" value="add_roles" class="btn btn-subtle-secondary">
                                Ajouter les Rôles
                            </button>
                            <button type="submit" name="action" value="add_type_structure" class="btn btn-subtle-info">
                                Ajouter les Types d'acteurs
                            </button>
                            <button type="submit" name="action" value="add_structure" class="btn btn-subtle-success">
                                Ajouter les Acteurs
                            </button>
                            <button type="submit" name="action" value="add_admin" class="btn btn-subtle-warning">
                                Ajouter l'Admin
                            </button>
                            <button type="submit" name="action" value="add_secteur" class="btn btn-subtle-info">
                                Ajouter les Secteurs
                            </button>
                            <button type="submit" name="action" value="add_niveaux" class="btn btn-subtle-danger">
                                Ajouter les Niveaux
                            </button>
                            <button type="submit" name="action" value="add_dossiers" class="btn btn-light">
                                Ajouter les Dossiers
                            </button>
                            <button type="submit" name="action" value="add_feuille_ligne_type" class="btn btn-subtle-primary">
                                Ajouter les Feuille Ligne Type
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <a href="./accueil.php" class="btn btn-link text-primary text-center w-100">Retour à l'accueil</a>
            <div class="text-center my-3">© <?php echo date('Y'); ?> MRV - Burundi. Tous droits reservés. Powered by <a class="text-danger fw-bolder" href="https://cosit-mali.com/" target="_blank">COSIT</a></div>
        </div>
    </div>
</body>

</html>