<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Détails de l'indicateur | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $refId = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$refId || !is_numeric($refId)) {
        echo "<script>window.location.href = 'referentiels.php';</script>";
        exit;
    }

    $referentiel = new Referentiel($db);
    $referentiel->id = $refId;
    $ref_curr = $referentiel->readById();

    if (!$ref_curr) {
        echo "<script>window.location.href = 'referentiels.php';</script>";
        exit;
    }

    $user = new User($db);
    $users = $user->read();

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });
    $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] > 0 && $secteur['state'] == 'actif';
    });

    $unite = new Unite($db);
    $unites = $unite->read();

    $province = new Province($db);
    $provinces = $province->read();

    $zone_type = new ZoneType($db);
    $zone_types = $zone_type->read();

    $typologie = new Typologie($db);
    $typologies = $typologie->read();

    $sens_evolutions = array('asc' => 'Ascendant', 'desc' => 'Descendant');
    $echelles = array('nationale' => 'Nationale', 'provincial' => 'Provincial');

    $cible_referentiel = new Cible($db);
    $cible_referentiel->indicateur_id = $ref_curr['id'];
    $cibles_raw = $cible_referentiel->readByIndicateur();

    $suivi_referentiel = new Suivi($db);
    $suivi_referentiel->indicateur_id = $ref_curr['id'];
    $suivis_raw = $suivi_referentiel->readByIndicateur();

    function calculerProgres($valeurs_annees, $annees_disponibles, $cible)
    {
        if (empty($valeurs_annees) || $cible <= 0) return 0;

        $derniere_valeur = 0;
        foreach ($annees_disponibles as $annee) {
            if (isset($valeurs_annees[$annee])) {
                $derniere_valeur = $valeurs_annees[$annee];
            }
        }

        return $derniere_valeur > 0 ? round(($derniere_valeur / $cible) * 100, 2) : 0;
    }

    $scenarios_data = array();
    $annees = array();
    if (!empty($cibles_raw) || !empty($suivis_raw)) {
        foreach ($cibles_raw as $cible) {
            $scenario = $cible['scenario'];
            $annee = $cible['annee'];
            $valeur = (float)$cible['valeur'];

            if (!isset($scenarios_data[$scenario])) {
                $scenarios_data[$scenario] = [
                    'name' => listTypeScenario()[$scenario] ?? $scenario,
                    'annees' => [],
                    'cible' => 0,
                    'total' => 0
                ];
            }

            $scenarios_data[$scenario]['annees'][$annee] = $valeur;
            $scenarios_data[$scenario]['total'] += $valeur;
        }

        $all_years = array();
        if (!empty($cibles_raw)) {
            $cible_years = array_column($cibles_raw, 'annee');
            $all_years = array_merge($all_years, $cible_years);
        }

        if (!empty($suivis_raw)) {
            $suivi_years = array_column($suivis_raw, 'annee');
            $all_years = array_merge($all_years, $suivi_years);
        }

        if (!empty($all_years)) {
            $min_year = min($all_years);
            $max_year = max($all_years);

            for ($year = $min_year; $year <= $max_year; $year++) {
                $annees[] = $year;
            }
        }
    }

    $cibles_map = array();
    $cibles_total = 0;
    if (!empty($cibles_raw)) {
        usort($cibles_raw, fn($a, $b) => $a['annee'] - $b['annee']);
        foreach ($cibles_raw as $item) {
            $year = $item['annee'];
            $value = (float)$item['valeur'];
            if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
            $cibles_map[$year] += $value;
            $cibles_total += $value;
        }
        ksort($cibles_map, SORT_NUMERIC);
    }

    $suivis_map = array();
    $suivis_total = 0;
    $suivis_par_annee = array();
    $suivis_par_scenario = array();

    if (!empty($suivis_raw)) {
        usort($suivis_raw, fn($a, $b) => $a['annee'] - $b['annee']);

        foreach ($suivis_raw as $item) {
            $year = $item['annee'];
            $value = (float)$item['valeur'];
            $scenario = $item['scenario'];

            if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
            $suivis_map[$year] += $value;
            $suivis_total += $value;

            if (!isset($suivis_par_scenario[$scenario])) {
                $suivis_par_scenario[$scenario] = 0;
            }
            $suivis_par_scenario[$scenario] += $value;

            if (!isset($suivis_par_annee[$year])) {
                $suivis_par_annee[$year] = array();
            }
            $suivis_par_annee[$year][] = $item;
        }
        ksort($suivis_map, SORT_NUMERIC);
    }

    $chart_data_scenario = array();
    if (!empty($suivis_par_scenario)) {
        foreach (listTypeScenario() as $key => $scenario) {
            $valeur = $suivis_par_scenario[$key] ?? 0;
            if ($valeur > 0) {
                $chart_data_scenario[] = [
                    'name' => $scenario,
                    'y' => $valeur,
                ];
            }
        }
    }

    $cibles_array = array();
    $suivis_array = array();
    if (!empty($annees)) {
        foreach ($annees as $year) {
            $cibles_array[] = (float)($cibles_map[$year] ?? 0);
            $suivis_array[] = (float)($suivis_map[$year] ?? 0);
        }
    }
    ?>
</head>

<body class="bg-light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content px-2 mt-n4">
            <div class="card rounded-1 mb-9 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($ref_curr['intitule']) ?></h4>
                            <div class="text-muted">
                                <span class="me-3">Code: <?= htmlspecialchars($ref_curr['code']) ?></span>
                                <span class="me-3">Créé le: <?= date('d/m/Y', strtotime($ref_curr['created_at'])) ?></span>
                                <span class="me-3">Créé par: <?= htmlspecialchars($users[$ref_curr['add_by']]['nom'] ?? 'Non défini') ?></span>
                            </div>
                        </div>
                        <div class="btn-reveal-trigger d-flex gap-2">
                            <button title="Voir les résultats" type="button" class="btn btn-sm btn-subtle-primary text-nowrap fs-10 px-2 py-1" onclick="window.location.href = 'referentiels.php';">
                                <span class="uil-arrow-left"></span> Retour
                            </button>

                            <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="Activer/Désactiver" onclick="updateState(<?php echo $ref_curr['id']; ?>, '<?php echo $ref_curr['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $ref_curr['state'] == 'actif' ? 'désactiver' : 'activer'; ?> cet indicateur ?', 'referentiels')"
                                    type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                    <span class="uil-<?php echo $ref_curr['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                                </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'delete')) : ?>
                                <button title="Supprimer" onclick="deleteData(<?php echo $ref_curr['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cet indicateur ?', 'referentiels')"
                                    type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                    <span class="uil-trash-alt fs-8"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Informations de l'indicateur</h5>

                                    <?php if (checkPermis($db, 'update')) : ?>
                                        <button title="Modifier" class="btn btn-sm btn-subtle-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#addReferentielModal" data-id="<?php echo $ref_curr['id']; ?>">
                                            <span class="uil-edit"></span> Modifier
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-3">
                                    <dl class="row mb-0 px-3 small">
                                        <div class="col-6 row border rounded-1 p-3 mx-0">
                                            <dt class="col-sm-4 text-muted">Code</dt>
                                            <dd class="col-sm-8 fw-semibold"><?= htmlspecialchars($ref_curr['code']) ?></dd>

                                            <dt class="col-sm-4 text-muted">Intitulé</dt>
                                            <dd class="col-sm-8"><?= htmlspecialchars($ref_curr['intitule']) ?></dd>

                                            <?php if (!empty($ref_curr['description'])) : ?>
                                                <dt class="col-sm-4 text-muted">Description</dt>
                                                <dd class="col-sm-8"><?= nl2br(htmlspecialchars($ref_curr['description'])) ?></dd>
                                            <?php endif; ?>

                                            <hr class="mb-2">
                                            <dt class="col-sm-4 text-muted">Catégorie</dt>
                                            <dd class="col-sm-8">
                                                <span class="badge bg-info text-uppercase">
                                                    <?= htmlspecialchars($ref_curr['categorie']) ?>
                                                </span>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Norme</dt>
                                            <dd class="col-sm-8">
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($ref_curr['norme']) ?>
                                                </span>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Unité</dt>
                                            <dd class="col-sm-8"><?= htmlspecialchars($ref_curr['unite']) ?></dd>

                                            <dt class="col-sm-4 text-muted">Fonction d’agrégation</dt>
                                            <dd class="col-sm-8">
                                                <?= listModeAggregation()[$ref_curr['fonction_agregation']] ?? htmlspecialchars($ref_curr['fonction_agregation']) ?>
                                            </dd>

                                            <hr class="mb-2">
                                            <dt class="col-sm-4 text-muted">Secteur</dt>
                                            <dd class="col-sm-8">
                                                <?php foreach ($secteurs as $secteur): ?>
                                                    <?php if ($secteur['id'] == $ref_curr['secteur_id']): ?>
                                                        <?= htmlspecialchars($secteur['name']) ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Sous secteur</dt>
                                            <dd class="col-sm-8">
                                                <?php foreach ($sous_secteurs as $secteur): ?>
                                                    <?php if ($secteur['id'] == $ref_curr['action']): ?>
                                                        <?= htmlspecialchars($secteur['name']) ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Échelle</dt>
                                            <dd class="col-sm-8">
                                                <?= $echelles[$ref_curr['echelle']] ?? htmlspecialchars($ref_curr['echelle']) ?>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Modèle</dt>
                                            <dd class="col-sm-8">
                                                <?= listModeleTypologie()[$ref_curr['modele']] ?? htmlspecialchars($ref_curr['modele']) ?>
                                            </dd>
                                        </div>

                                        <div class="col-6 row border rounded-1 p-3 mx-0">
                                            <dt class="col-sm-4 text-muted">Responsables</dt>
                                            <dd class="col-sm-8">
                                                <?php
                                                $responsables = [];

                                                if (!empty($ref_curr['responsable'])) {
                                                    foreach ($structures as $s) {
                                                        if ($s['id'] == $ref_curr['responsable']) {
                                                            $responsables[] = "<span class='badge bg-primary'>{$s['sigle']}</span>";
                                                        }
                                                    }
                                                }

                                                echo $responsables ? implode(' ', $responsables) : '<em class="text-muted">Non défini</em>';
                                                ?>
                                            </dd>

                                            <hr class="mb-2">
                                            <?php if ($ref_curr['seuil_min'] > 0 || $ref_curr['seuil_max'] > 0) : ?>
                                                <dt class="col-sm-4 text-muted">Seuils</dt>
                                                <dd class="col-sm-8">
                                                    <?= $ref_curr['seuil_min'] > 0 ? "Min : {$ref_curr['seuil_min']}" : '' ?>
                                                    <?= $ref_curr['seuil_max'] > 0 ? " | Max : {$ref_curr['seuil_max']}" : '' ?>
                                                </dd>
                                            <?php endif; ?>

                                            <dt class="col-sm-4 text-muted">Période</dt>
                                            <dd class="col-sm-8">
                                                <?= $ref_curr['annee_debut'] ?: '?' ?> – <?= $ref_curr['annee_fin'] ?: '?' ?>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Sens d’évolution</dt>
                                            <dd class="col-sm-8">
                                                <?= $sens_evolutions[$ref_curr['sens_evolution']] ?? htmlspecialchars($ref_curr['sens_evolution']) ?>
                                            </dd>

                                            <hr class="mb-2">
                                            <dt class="col-sm-4 text-muted">Tableau de bord</dt>
                                            <dd class="col-sm-8">
                                                <span class="badge bg-<?= $ref_curr['in_dashboard'] ? 'success' : 'secondary' ?>">
                                                    <?= $ref_curr['in_dashboard'] ? 'Visible' : 'Masqué' ?>
                                                </span>
                                            </dd>

                                            <dt class="col-sm-4 text-muted">Dernière mise à jour</dt>
                                            <dd class="col-sm-8">
                                                <i class="bi bi-clock"></i>
                                                <?= date('d/m/Y H:i', strtotime($ref_curr['updated_at'])) ?>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="card rounded-1 shadow-sm border">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Valeurs cibles de l'indicateur</h5>

                                    <?php if (checkPermis($db, 'create')) : ?>
                                        <button title="Nouvelle cible" type="button" class="btn btn-sm btn-subtle-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#newIndicateurCibleModal" data-indicateur_id="<?php echo $ref_curr['id']; ?>">
                                            <span class="uil-plus"></span> Nouvelle cible
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-2" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($cibles_raw)) : ?>
                                        <table class="table table-sm table-hover table-striped small table-bordered border-emphasis small" id="ciblePivotTable">
                                            <thead class="bg-primary-subtle">
                                                <tr>
                                                    <th scope="col" class="px-2 text-center" rowspan="2">Scénario</th>
                                                    <?php foreach ($annees as $year): ?>
                                                        <th scope="col" class="px-2 text-center" rowspan="2"><?= $year ?></th>
                                                    <?php endforeach; ?>
                                                    <th scope="col" class="px-2 text-center" rowspan="2">Cible pour 2025</th>
                                                    <th scope="col" class="px-2 text-center" colspan="2">Progrès</th>
                                                </tr>
                                                <tr>
                                                    <th scope="col" class="px-2 text-center">(en valeur)</th>
                                                    <th scope="col" class="px-2 text-center">(en %)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach (listTypeScenario() as $scenario_key => $scenario_value):
                                                    if (isset($scenarios_data[$scenario_key])):
                                                        $data = $scenarios_data[$scenario_key];
                                                        $progres_valeur = !empty($data['annees']) ? end($data['annees']) : 0;
                                                        $progres_pourcentage = calculerProgres($data['annees'], $annees, $data['total']);
                                                ?>
                                                        <tr class="align-middle">
                                                            <td class="px-2 fw-bold">
                                                                <span class="badge bg-<?= $scenario_key == 'bau' ? 'primary' : ($scenario_key == 'wem' ? 'warning' : 'success') ?>">
                                                                    <?= $data['name'] ?>
                                                                </span>
                                                            </td>

                                                            <?php foreach ($annees as $year): ?>
                                                                <td class="px-2 text-end">
                                                                    <?= isset($data['annees'][$year]) ? number_format($data['annees'][$year], 2) : '0,00' ?>
                                                                </td>
                                                            <?php endforeach; ?>

                                                            <td class="px-2 text-end fw-bold text-success">
                                                                <?= number_format($data['total'], 2) ?>
                                                            </td>

                                                            <td class="px-2 text-end fw-bold">
                                                                <?= number_format($progres_valeur, 2) ?>
                                                            </td>

                                                            <td class="px-2 text-end fw-bold <?= $progres_pourcentage >= 100 ? 'text-success' : ($progres_pourcentage >= 80 ? 'text-warning' : 'text-danger') ?>">
                                                                <?= number_format($progres_pourcentage, 2) ?>%
                                                            </td>
                                                        </tr>
                                                <?php
                                                    endif;
                                                endforeach;
                                                ?>
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <td class="text-end fw-bold">Total :</td>
                                                    <?php
                                                    $totaux_annees = [];
                                                    foreach ($annees as $year) {
                                                        $total_annee = 0;
                                                        foreach ($scenarios_data as $data) {
                                                            $total_annee += isset($data['annees'][$year]) ? $data['annees'][$year] : 0;
                                                        }
                                                        $totaux_annees[$year] = $total_annee;
                                                    }

                                                    foreach ($annees as $year): ?>
                                                        <td class="text-end fw-bold">
                                                            <?= number_format($totaux_annees[$year], 2) ?>
                                                        </td>
                                                    <?php endforeach; ?>

                                                    <td class="text-end fw-bold text-success">
                                                        <?= number_format($cibles_total, 2) ?>
                                                    </td>
                                                    <td class="text-end fw-bold">
                                                        <?= number_format(array_sum(array_column($scenarios_data, 'total')), 2) ?>
                                                    </td>
                                                    <td class="text-end fw-bold">
                                                        <!-- Progrès total moyen -->
                                                        <?php
                                                        $total_progres = 0;
                                                        $count = 0;
                                                        foreach ($scenarios_data as $data) {
                                                            $progres = calculerProgres($data['annees'], $annees, $data['total']);
                                                            if ($progres > 0) {
                                                                $total_progres += $progres;
                                                                $count++;
                                                            }
                                                        }
                                                        $moyenne_progres = $count > 0 ? $total_progres / $count : 0;
                                                        ?>
                                                        <?= number_format($moyenne_progres, 2) ?>%
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    <?php else : ?>
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <span class="uil-database display-4 text-muted"></span>
                                            </div>
                                            <h5 class="text-muted">Aucune valeur cible enregistrée</h5>
                                            <p class="text-muted fs-9 mb-0">Ajoutez des valeurs cible pour cet indicateur</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Suivis de l'indicateur</h5>

                                    <?php if (checkPermis($db, 'create')) : ?>
                                        <button title="Nouvelle valeur" type="button" class="btn btn-sm btn-subtle-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#newIndicateurSuiviModal" data-indicateur_id="<?php echo $ref_curr['id']; ?>">
                                            <span class="uil-plus"></span> Nouvelle valeur
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-2" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($suivis_raw)) : ?>
                                        <table class="table table-sm table-hover table-striped small table-bordered border-emphasis">
                                            <thead class="bg-primary-subtle">
                                                <tr>
                                                    <th scope="col" class="px-2">Année</th>
                                                    <th scope="col" class="px-2">Valeur</th>
                                                    <th scope="col" class="px-2">Scénario</th>
                                                    <?php if ($ref_curr['echelle'] !== 'nationale') : ?>
                                                        <th scope="col" class="px-2">Échelle</th>
                                                    <?php endif; ?>
                                                    <?php if ($ref_curr['modele'] !== 'valeur_absolue') : ?>
                                                        <th scope="col" class="px-2">Classe</th>
                                                    <?php endif; ?>
                                                    <th scope="col" class="px-2">Date suivie</th>
                                                    <th scope="col" class="px-2">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($suivis_raw as $suivi): ?>
                                                    <tr class="align-middle">
                                                        <td class="px-2"><?= $suivi['annee'] ?></td>
                                                        <td class="px-2 fw-bold"><?= number_format($suivi['valeur'], 2) ?></td>
                                                        <td class="px-2">
                                                            <?= listTypeScenario()[$suivi['scenario']] ?? $suivi['scenario'] ?>
                                                        </td>
                                                        <?php if ($ref_curr['echelle'] !== 'nationale') : ?>
                                                            <td class="px-2">
                                                                <?php
                                                                if ($ref_curr['echelle'] == 'provincial') {
                                                                    foreach ($provinces as $province) {
                                                                        if ($province['code'] == $suivi['echelle']) {
                                                                            echo $province['name'];
                                                                            break;
                                                                        }
                                                                    }
                                                                } else echo htmlspecialchars($suivi['echelle']);
                                                                ?>
                                                            </td>
                                                        <?php endif; ?>
                                                        <?php if ($ref_curr['modele'] !== 'valeur_absolue') : ?>
                                                            <td class="px-2"><?= htmlspecialchars($suivi['classe']) ?></td>
                                                        <?php endif; ?>
                                                        <td class="px-2"><?= date('d/m/Y', strtotime($suivi['date_suivie'])) ?></td>
                                                        <td class="px-2">
                                                            <?php if (!empty($suivi['observation'])) : ?>
                                                                <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($suivi['observation']) ?>">
                                                                    <span class="uil-comment-alt"></span>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <td colspan="<?= $ref_curr['echelle'] == 'nationale' ? ($ref_curr['modele'] == 'valeur_absolue' ? 1 : 2) : ($ref_curr['modele'] == 'valeur_absolue' ? 2 : 3) ?>" class="text-end fw-bold">Total :</td>
                                                    <td class="fw-bold text-primary"><?= number_format($suivis_total, 2) ?></td>
                                                    <td colspan="4"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    <?php else : ?>
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <span class="uil-database display-4 text-muted"></span>
                                            </div>
                                            <h5 class="text-muted">Aucun suivi enregistré</h5>
                                            <p class="text-muted fs-9 mb-0">Ajoutez des données de suivi pour cet indicateur</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border">
                                <div class="card-header bg-light dark__bg-dark rounded-0 py-3 pb-2 px-3 d-flex align-items-center">
                                    <h5 class="mb-0">Synthèse des données par année</h5>
                                </div>
                                <div class="card-body p-2" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($suivis_par_annee)) : ?>
                                        <table class="table table-sm table-hover table-striped small table-bordered border-emphasis">
                                            <thead class="bg-primary-subtle">
                                                <tr>
                                                    <th class="text-center">Année</th>
                                                    <th class="text-center">Cible totale</th>
                                                    <th class="text-center">Suivi total</th>
                                                    <th class="text-center">Taux de réalisation</th>
                                                    <th class="text-center">Écart</th>
                                                    <th class="text-center">Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_cibles = 0;
                                                $total_suivis = 0;

                                                foreach ($annees as $annee):
                                                    $cible_annee = $cibles_map[$annee] ?? 0;
                                                    $suivi_annee = $suivis_map[$annee] ?? 0;
                                                    $taux = $cible_annee > 0 ? ($suivi_annee / $cible_annee) * 100 : 0;
                                                    $ecart = $suivi_annee - $cible_annee;

                                                    $total_cibles += $cible_annee;
                                                    $total_suivis += $suivi_annee;
                                                ?>
                                                    <tr>
                                                        <td class="text-center fw-bold"><?= $annee ?></td>
                                                        <td class="text-center"><?= $cible_annee > 0 ? number_format($cible_annee, 2) : '-' ?></td>
                                                        <td class="text-center fw-bold"><?= number_format($suivi_annee, 2) ?></td>
                                                        <td class="text-center">
                                                            <?php if ($cible_annee > 0): ?>
                                                                <span class="badge bg-<?= $taux >= 100 ? 'success' : ($taux >= 80 ? 'warning' : 'danger') ?>">
                                                                    <?= number_format($taux, 1) ?>%
                                                                </span>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>">
                                                            <?= $ecart >= 0 ? '+' : '' ?><?= number_format($ecart, 2) ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($cible_annee > 0): ?>
                                                                <?php if ($taux >= 100): ?>
                                                                    <span class="badge bg-success">Atteinte</span>
                                                                <?php elseif ($taux >= 80): ?>
                                                                    <span class="badge bg-warning">Partielle</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">Non atteinte</span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Pas de cible</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="bg-light fw-bold">
                                                <tr>
                                                    <td class="text-center">Total</td>
                                                    <td class="text-center"><?= number_format($total_cibles, 2) ?></td>
                                                    <td class="text-center"><?= number_format($total_suivis, 2) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($total_cibles > 0): ?>
                                                            <?php
                                                            $taux_total = ($total_suivis / $total_cibles) * 100;
                                                            $badge_class = $taux_total >= 100 ? 'success' : ($taux_total >= 80 ? 'warning' : 'danger');
                                                            ?>
                                                            <span class="badge bg-<?= $badge_class ?>">
                                                                <?= number_format($taux_total, 1) ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center <?= ($total_suivis - $total_cibles) >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= ($total_suivis - $total_cibles) >= 0 ? '+' : '' ?><?= number_format($total_suivis - $total_cibles, 2) ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($total_cibles > 0): ?>
                                                            <?php if ($taux_total >= 100): ?>
                                                                <span class="badge bg-success">Atteinte</span>
                                                            <?php elseif ($taux_total >= 80): ?>
                                                                <span class="badge bg-warning">Partielle</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Non atteinte</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Pas de cible</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    <?php else : ?>
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <span class="uil-database display-4 text-muted"></span>
                                            </div>
                                            <h5 class="text-muted">Aucun suivi enregistré</h5>
                                            <p class="text-muted fs-9 mb-0">Ajoutez des données de suivi pour cet indicateur</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($annees)) : ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card rounded-1 shadow-sm border h-100">
                                    <div class="card-header bg-light dark__bg-dark rounded-0 py-2 px-3">
                                        <h5 class="mb-0">Évolution annuelle des données</h5>
                                    </div>
                                    <div class="card-body p-2" id="chartAnnee<?= $ref_curr['id'] ?>"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card rounded-1 shadow-sm border h-100">
                                    <div class="card-header bg-light dark__bg-dark rounded-0 py-2 px-3">
                                        <h5 class="mb-0">Répartition des suivis par scénario</h5>
                                    </div>
                                    <div class="card-body p-2" id="chartScenario<?= $ref_curr['id'] ?>"></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>
</body>

<?php if (!empty($annees)) : ?>
    <script>
        mrvColumnChart({
            id: 'chartAnnee<?= $ref_curr['id'] ?>',
            title: 'Évolution annuelle - <?= htmlspecialchars($ref_curr['intitule']) ?>',
            unite: '<?= htmlspecialchars($ref_curr['unite'] ?? '') ?>',
            categories: <?= json_encode($annees ?? []) ?>,
            cibles: <?= json_encode($cibles_array ?? []) ?>,
            suivis: <?= json_encode($suivis_array ?? []) ?>
        });

        <?php if (!empty($chart_data_scenario)) : ?>
            mrvPieChart({
                id: 'chartScenario<?= $ref_curr['id'] ?>',
                title: 'Répartition par scénario',
                unite: '<?= htmlspecialchars($ref_curr['unite'] ?? '') ?>',
                data: <?= json_encode($chart_data_scenario ?? []) ?>,
            });
        <?php endif; ?>
    </script>
<?php endif; ?>

</html>