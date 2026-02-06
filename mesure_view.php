<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Détails Mesure | MRV - Burundi</title>
    <?php include './components/navbar & footer/head.php'; ?>
    <?php
    $mesure_id = $_GET['id'] ?? 0;
    $mesure = new Mesure($db);
    $mesure->id = $mesure_id;
    $mesure_curr = $mesure->readById();

    if (!$mesure_curr) {
        echo ('<script>window.location.href = "mesures.php"</script>');
        exit();
    }

    $cible_mesure = new Cible($db);
    $cible_mesure->mesure_id = $mesure_curr['id'];
    $cibles_raw = $cible_mesure->readByMesure();

    $suivi_mesure = new Suivi($db);
    $suivi_mesure->mesure_id = $mesure_curr['id'];
    $suivis_raw = $suivi_mesure->readByMesure();

    $suivis_map = array();
    $suivis_total = 0;
    $suivis_par_annee = array();
    $suivis_par_scenario = array();

    $annees = array();
    if (!empty($cibles_raw) || !empty($suivis_raw)) {
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


    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $referentiel = new Referentiel($db);
    $referentiels = $referentiel->read();
    $referentiels_mesure = array_filter($referentiels, function ($referentiel) {
        return $referentiel['state'] == 'actif';
    });


    $gaz = new Gaz($db);
    $gazs = $gaz->read();
    $gaz_colors = [];
    foreach ($gazs as $g) {
        $gaz_colors[strtoupper($g['name'])] = $g['couleur'] ?: '#' . substr(md5($g['name']), 0, 6);
    }

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs_mesure = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });

    ?>

    <style>
        .gauge-conic {
            background: conic-gradient(from 180deg, #13ec5b 0% <?= $mesure_curr['progression'] ?? 0 ?>%, #1c2e22 <?= $mesure_curr['progression'] ?? 0 ?>% 100%)
        }
    </style>
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content px-2 mt-n4">
            <div class="card rounded-1 mb-9 overflow-hidden">
                <div class="card-body p-3">

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge badge-phoenix badge-phoenix-info rounded-pill">
                                    <?php foreach ($secteurs_mesure as $secteur) : ?>
                                        <?php if ($secteur['id'] == $mesure_curr['secteur_id']) : ?>
                                            <?= $secteur['name'] ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </span>
                                <span class="badge badge-phoenix badge-phoenix-primary rounded-pill">
                                    <?= $mesure_curr['status'] ?>
                                </span>
                            </div>
                            <div class="d-flex align-items-end gap-1">
                                <button type="button" onclick="window.location.href=`<?= $_SERVER['PHP_SELF'] ?>`" class="btn btn-subtle-primary btn-sm">
                                    <span class="fa fa-arrow-left fs-9 me-2"></span>Retour
                                </button>

                                <?php if (checkPermis($db, 'update')) : ?>
                                    <button title="Modifier" class="btn btn-sm btn-phoenix-info" data-bs-toggle="modal"
                                        data-bs-target="#addMesureModal" data-id="<?= $mesure_curr['id'] ?>">
                                        <span class="uil-pen fs-9"></span>
                                    </button>
                                <?php endif; ?>

                                <?php if (checkPermis($db, 'delete', 2)) : ?>
                                    <button title="Supprimer" class="btn btn-sm btn-phoenix-danger" type="button"
                                        onclick="deleteData(<?= $mesure_curr['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette mesure ?', 'mesures')">
                                        <span class="uil-trash-alt fs-9"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h4 class="fw-bold mb-2"><?= $mesure_curr['name'] ?></h4>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-lg-8">
                            <div class="card mb-3 rounded-1 overflow-hidden">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-2 px-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fa fa-info-circle text-primary"></span>
                                        <h3 class="h6 mb-0 fw-bold">Description</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-body mb-0">
                                        <?= $mesure_curr['description'] ?>
                                    </p>
                                </div>
                            </div>

                            <div class="card mb-3 rounded-1 overflow-hidden">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-2 px-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fa fa-bullseye text-primary"></span>
                                        <h3 class="h6 mb-0 fw-bold">Objectifs</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- <ul class="list-unstyled mb-0">
                                    <php foreach ($mesure_curr['objectifs_details'] as $objectif): ?>
                                        <li class="d-flex align-items-start gap-3 mb-3">
                                            <span class="fa fa-check-circle text-primary mt-1"></span>
                                            <span class="text-body"><?= $objectif ?></span>
                                        </li>
                                    <php endforeach; ?>
                                </ul> -->
                                    <p class="text-body mb-0"><?= $mesure_curr['objectif'] ?></p>
                                </div>
                            </div>

                            <div class="card mb-3 rounded-1">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-2 px-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="far fa-list-alt text-primary"></span>
                                        <h3 class="h6 mb-0 fw-bold">Informations techniques</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Année de début</p>
                                            <p class="fw-semibold mb-0"><?= $mesure_curr['annee_debut'] ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Gaz concernés</p>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach (explode(',', $mesure_curr['gaz']) as $gaz) : ?>
                                                    <span class="badge rounded-1" style="background-color: <?php echo $gaz_colors[strtoupper($gaz)] ?? '#6c757d'; ?>; color: white;">
                                                        <?php echo $gaz; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Estimation évitée</p>
                                            <p class="fw-bold text-success mb-0">
                                                <?= $mesure_curr['valeur_estimee'] ?? 0 ?> Gg Eq.CO2
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card mb-3 rounded-1 overflow-hidden">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-2 px-2">
                                    <h3 class="h6 mb-0 fw-bold">Réalisations vs Prévisions</h3>
                                    <span class="badge bg-primary rounded-pill">Cible <?= date("Y") ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-center py-3">
                                        <div class="position-relative">
                                            <div class="gauge-conic rounded-circle" style="width: 200px; height: 200px;"></div>
                                            <div class="position-absolute top-50 start-50 translate-middle bg-primary-subtle rounded-circle d-flex flex-column align-items-center justify-content-center shadow-inner" style="width: 150px; height: 150px;">
                                                <span class="fs-4 fw-bold text-primary"><?= $mesure_curr['progression'] ?? 0 ?>%</span>
                                                <span class="text-uppercase text-muted small fw-bold">Progression</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4 pt-3 border-top">
                                        <div class="col-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Réalisé</p>
                                            <p class="h5 fw-bold mb-0">
                                                <?= number_format($mesure_curr['realise_co2'] ?? 0, 0, ',', ' ') ?> tCO2e
                                            </p>
                                        </div>
                                        <div class="col-6 text-end">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Prévision <?= date("Y") ?></p>
                                            <p class="h5 fw-bold text-body-tertiary mb-0">
                                                <?= number_format($mesure_curr['prevision_2025_co2'] ?? 0, 0, ',', ' ') ?> tCO2e
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Suivis de la mesure</h5>

                                    <?php if (checkPermis($db, 'create')) : ?>
                                        <button title="Nouvelle valeur" type="button" class="btn btn-sm btn-subtle-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#newIndicateurSuiviModal" data-mesure_id="<?php echo $mesure_curr['id']; ?>" data-indicateur_id="<?php echo $mesure_curr['referentiel_id']; ?>">
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
                                                    <td class="text-end fw-bold">Total :</td>
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
                                            <p class="text-muted fs-9 mb-0">Ajoutez des données de suivi pour cette mesure</p>
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
                                            <p class="text-muted fs-9 mb-0">Ajoutez des données de suivi pour cette mesure</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
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

</html>