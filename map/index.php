<?php
// Importation des fichies de configuration
$path = '../';
include_once $path . 'config/database.php';
include_once $path . 'config/functions.php';
include_once $path . 'services/user.mailer.php';

// Importation des models
$modelsDir = $path . 'models';
foreach (glob("$modelsDir/*.php") as $modelFile) {
    include_once $modelFile;
}

//========================= Database
$database = new Database();
$db = $database->getConnection();

//========================= Projets
$projet = new Projet($db);
$projets = $projet->read();
$projets_actifs = array_filter($projets, function ($projet) {
    return $projet['state'] == 'actif';
});

//========================= Projets ID
$projets_par_id = [];
foreach ($projets_actifs as $p) {
    $projets_par_id[$p['id']] = $p;
}

$projets_par_action = [];
foreach ($projets_actifs as $p) {
    $projets_par_action[$p['action_type']][] = $p;
}
$projets_actions_assoc = array_column($projets_actifs, 'action_type', 'action_type');

//========================= Indicateurs 
$indicateur = new Indicateur($db);
$indicateurs = $indicateur->read();
$indicateur_cmr = array_filter($indicateurs, function ($indicateur) {
    return $indicateur['state'] == 'actif';
});

$suivis_assoc = [];
foreach ($indicateur_cmr as $cmr) {
    $suivi = new Suivi($db);
    $suivi->cmr_id = $cmr['id'];
    $suivis_cmr = $suivi->readByCMR();
    $suivis_cmr_grouped = array();
    $suivis_calcul = 0;

    foreach ($suivis_cmr as $suivi) {
        $suivis_cmr_grouped[$suivi['annee']][] = $suivi;
    }
    foreach ($suivis_cmr_grouped as $annee => $suivis) {
        $suivis_calcul += calculSuiviData($suivis_cmr_grouped[$annee] ?? [], $cmr['mode_calcul']);
    }
    $suivis_assoc[$cmr['id']] = $suivis_calcul;
}

//========================= Unites 
$unite = new Unite($db);
$unites = $unite->read();
$unites_assoc = array_column($unites, 'name', 'id');

//========================= Structures
$structure = new Structure($db);
$structures = $structure->read();
$structures_assoc = array_column($structures, 'sigle', 'id');

//========================= Secteurs 
$secteur = new Secteur($db);
$secteurs = $secteur->read();
$secteurs_assoc = array_column($secteurs, 'name', 'id');

//========================= Provinces 
$province = new Province($db);
$provinces = $province->read();
$provinces_assoc = array_column($provinces, 'name', 'id');

//========================= Zones 
$zone = new Zone($db);
$zones = $zone->read();
$zones_assoc = array_column($zones, 'name', 'id');
$zonesCollectes = sort($zones);

// $extensions = ['zip', 'shp', 'shx', 'dbf', 'prj', 'sbn', 'sbx', 'fbn', 'fbx', 'ain', 'aih', 'ixs', 'mxs', 'atx', 'mtx'];
// $zonesCollectes = [];
// foreach ($extensions as $ext) {
//     $zonesCollectes = array_merge($zonesCollectes, glob("../uploads/couches/*.$ext"));
// }

//========================= Projets par province
$projets_par_province = [];
foreach ($indicateur_cmr as $indicateur) {
    if (!empty($indicateur['province']) && isset($projets_par_id[$indicateur['projet_id']])) {
        $province = $indicateur['province'];
        if (!isset($projets_par_province[$province])) {
            $projets_par_province[$province] = [
                'projets' => [],
                'budget_total' => 0,
                'indicateurs_count' => 0
            ];
        }

        $projet_id = $indicateur['projet_id'];
        if (!in_array($projet_id, $projets_par_province[$province]['projets'])) {
            $projets_par_province[$province]['projets'][] = $projet_id;
            $projets_par_province[$province]['budget_total'] += $projets_par_id[$projet_id]['budget'] ?? 0;
        }
        $projets_par_province[$province]['indicateurs_count']++;
    }
}
?>

<!-- ========================== Styles ========================== -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />

<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet" />
<link href="../assets/css/theme-rtl.css" type="text/css" rel="stylesheet" id="style-rtl" />
<link href="../assets/css/theme.min.css" type="text/css" rel="stylesheet" id="style-default" />

<!-- ========================== Leaflet ========================== -->
<link rel="stylesheet" href="styles.css" type="text/css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<!-- ========================== Scripts ========================== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php include 'scripts.php'; ?>

<section class="m-0 p-0">
    <div class="row m-0 g-3">
        <div class="col-md-3">
            <div class="card rounded-1 shadow-sm h-100">
                <div class="card-header rounded-top-1 px-3 py-2 bg-primary">
                    <h5 class="mb-0 text-white"><i class="fas fa-filter me-2"></i>Filtrer les données</h5>
                </div>

                <div class="card-body p-3" id="sidebar">
                    <!-- Filtre des données -->
                    <div class="mb-2">
                        <label for="filterAction" class="form-label px-2">Action</label>
                        <select id="filterAction" class="form-select mb-2" required>
                            <option value="">Toutes les actions</option>
                            <?php foreach ($projets_actions_assoc as $action) {
                                echo "<option value='{$action}'>" . htmlspecialchars($action) . "</option>";
                            } ?>
                        </select>

                        <label for="filterSecteur" class="form-label px-2">Secteur</label>
                        <select id="filterSecteur" class="form-select mb-2" required>
                            <option value="">Tous les secteurs</option>
                            <?php foreach ($secteurs_assoc as $id => $name): ?>
                                <option value="<?= $id ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="filterScenario" class="form-label px-2">Scénario</label>
                        <select id="filterScenario" class="form-select mb-2" required>
                            <option value="">Tous les scénarios</option>
                            <option value="conditionnel">Conditionnel</option>
                            <option value="inconditionnel">Inconditionnel</option>
                        </select>

                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <button type="button" id="filterMapBtn" class="btn btn-sm btn-primary">Appliquer</button>
                            <button type="button" id="resetMapBtn" class="btn btn-sm btn-secondary">Réinitialiser</button>
                        </div>
                    </div>

                    <!-- Statistiques rapides -->
                    <div class="mt-3 pt-3 border-top">
                        <div id="nav-controls">
                            <ul class="nav nav-tabs nav-fill" id="mapPanelTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fs-10 text-white active" id="couches-tab" data-bs-toggle="tab" data-bs-target="#couches"
                                        type="button" role="tab" aria-controls="couches" aria-selected="true">
                                        Couches <span class="badge bg-success" id="provincesCount"></span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fs-10 text-white" id="zones-tab" data-bs-toggle="tab" data-bs-target="#zones"
                                        type="button" role="tab" aria-controls="zones" aria-selected="false">
                                        Zones <span class="badge bg-success" id="zonesCount">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fs-10 text-white" id="actions-tab" data-bs-toggle="tab" data-bs-target="#actions"
                                        type="button" role="tab" aria-controls="actions" aria-selected="false">
                                        Actions <span class="badge bg-success" id="actionsCount"><?= count($projets_actifs ?? []) ?></span>
                                    </button>
                                </li>
                                <!-- <li class="nav-item" role="presentation">
                                    <button class="nav-link fs-10 text-white" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats"
                                        type="button" role="tab" aria-controls="stats" aria-selected="false">Statistiques</button>
                                </li> -->
                            </ul>
                            <div class="tab-content" id="mapPanelTabContent">
                                <div class="tab-pane fade show active" id="couches" role="tabpanel" aria-labelledby="couches-tab">
                                    <div id="provincesList" class="list-group small">
                                        <div class="list-group-item list-group-item-action bg-primary-subtle">
                                            <div class="input-group">
                                                <input type="checkbox" id="all-provinces" name="provinces[]" value="all">
                                                <label for="all-provinces" class="text-capitalize fw-semibold link-primary cursor-pointer mx-1 w-75">Tout cocher</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="zones" role="tabpanel" aria-labelledby="zones-tab">
                                    <div id="zonesList" class="list-group small">
                                        <div class="list-group-item list-group-item-action bg-primary-subtle">
                                            <div class="input-group">
                                                <input type="checkbox" id="all-zones" name="zones[]" value="all">
                                                <label for="all-zones" class="text-capitalize fw-semibold link-primary cursor-pointer mx-1 w-75">Tout cocher</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="actions" role="tabpanel" aria-labelledby="actions-tab">
                                    <div id="projetsList" class="list-group small">
                                        <div class="list-group-item list-group-item-action bg-primary-subtle">
                                            <div class="input-group">
                                                <input type="checkbox" id="all-projets" name="projets[]" value="all">
                                                <label for="all-projets" class="text-capitalize fw-semibold link-primary cursor-pointer mx-1 w-75">Tout cocher</label>
                                            </div>
                                        </div>
                                        <?php foreach ($projets_actifs as $projet) { ?>
                                            <div class="list-group-item list-group-item-action">
                                                <div class="input-group">
                                                    <input type="checkbox" class="proj-checkbox" id="proj-<?= $projet['id'] ?>" name="projets[]" value="<?= $projet['id'] ?>">
                                                    <label for="proj-<?= $projet['id'] ?>" class="text-capitalize text-truncate text-nowrap link-primary cursor-pointer mx-1 w-75">
                                                        <?= $projet['name'] ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <!-- <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                                    <div class="list-group small">
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <span>Projets actifs</span>
                                            <span class="badge bg-success"><?= count($projets_actifs) ?></span>
                                        </div>
                                        <?php foreach ($projets_actions_assoc as $action) { ?>
                                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <span class="text-capitalize"><?= htmlspecialchars($action) ?></span>
                                                <span class="badge bg-primary"><?= count($projets_par_action[$action]) ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card rounded-1 shadow-sm h-100">
                <div class="card-header rounded-top-1 px-3 py-2 bg-primary">
                    <h5 class="mb-0 text-white"><i class="fas fa-map-marked-alt me-2"></i>Répartition Géographique des Projets</h5>
                </div>

                <div class="card-body p-2 position-relative">
                    <div id="mapContainer">
                        <!-- Barre de progression pour le chargement -->
                        <div class="progress-indicator" id="progressIndicator"></div>

                        <!-- Contrôles de carte -->
                        <div class="map-controls">
                            <button class="map-control-btn" id="locateUser" title="Me localiser">
                                <i class="fas fa-crosshairs"></i>
                            </button>
                            <button class="map-control-btn" id="zoomIn" title="Zoomer">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="map-control-btn" id="zoomOut" title="Dézoomer">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="map-control-btn" id="toggleFullscreen" title="Plein écran">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>

                        <!-- Recherche -->
                        <div class="search-container">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Rechercher un projet...">
                                <button class="btn btn-primary btn-sm" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="searchResults" class="mt-2 d-none"></div>
                        </div>

                        <!-- Panneau de statistiques -->
                        <div class="stats-panel d-none" id="statsPanel">
                            <h6 id="statsProvinceName">Statistiques de la zone</h6>
                            <div class="stats-item">
                                <span>Projets:</span>
                                <span id="zoneProjects">0</span>
                            </div>
                            <div class="stats-item">
                                <span>Budget total:</span>
                                <span id="zoneBudget">0 FCFA</span>
                            </div>
                            <div class="stats-item">
                                <span>Indicateurs:</span>
                                <span id="zoneIndicators">0</span>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary mt-2" id="closeStatsPanel">
                                <i class="fas fa-times"></i> Fermer
                            </button>
                        </div>

                        <!-- Sélecteur de fond de carte -->
                        <div class="basemap-selector">
                            <label for="basemapSelect" class="form-label mb-1"><small>Fond de carte:</small></label>
                            <select id="basemapSelect" class="form-select form-select-sm">
                                <option value="osm">OpenStreetMap</option>
                                <option value="satellite">Satellite</option>
                                <option value="dark">Carte Sombre</option>
                                <option value="terrain">Relief</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================== Scripts ========================== -->
<script src="../vendors/bootstrap/bootstrap.min.js"></script>
<script src="../vendors/fontawesome/all.min.js"></script>
<script src="../vendors/feather-icons/feather.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

<!-- ========================== Scripts leaflet ========================== -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/shpjs@latest/dist/shp.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
