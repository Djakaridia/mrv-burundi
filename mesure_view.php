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

    $projection = new Projection($db);
    $projections = $projection->read();
    $projections_grouped = [];
    foreach ($projections as $projection) {
        $secteur = $projection['secteur_id'];
        $annee = $projection['annee'];
        $scenario = $projection['scenario'];
        $valeur = str_replace(',', '.', $projection['valeur']);
        $projections_grouped[$secteur][$annee][$scenario] = floatval($valeur);
    }

    $unite = new Unite($db);
    $unites = $unite->read();

    $suivi_mesure = new Suivi($db);
    $suivi_mesure->mesure_id = $mesure_curr['id'];
    $suivis_raw = $suivi_mesure->readByMesure();

    $annees = array();
    $suivis_map = array();
    $suivis_total = 0;

    if (!empty($suivis_raw)) {
        $all_years = array();
        usort($suivis_raw, fn($a, $b) => $a['annee'] - $b['annee']);

        $suivi_years = array_column($suivis_raw, 'annee');
        $all_years = array_merge($all_years, $suivi_years);

        if (!empty($all_years)) {
            $min_year = min($all_years);
            $max_year = max($all_years);

            for ($year = $min_year; $year <= $max_year; $year++) {
                $annees[] = $year;
            }
        }

        foreach ($suivis_raw as $item) {
            $year = $item['annee'];
            $value = (float)$item['valeur'];
            $scenario = $item['scenario'];

            if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
            $suivis_map[$year] += $value;
            $suivis_total += $value;
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
                                    <?= listStatus()[$mesure_curr['status']]??"N/A" ?>
                                </span>
                            </div>
                            <div class="d-flex align-items-end gap-1">
                                <button type="button" onclick="window.location.href=`<?= $_SERVER['PHP_SELF'] ?>`" class="btn btn-subtle-primary btn-sm p-2">
                                    <span class="fa fa-arrow-left fs-9 me-2"></span>Retour
                                </button>

                                <?php if (checkPermis($db, 'update')) : ?>
                                    <button title="Modifier" class="btn btn-sm btn-phoenix-info p-2" data-bs-toggle="modal"
                                        data-bs-target="#addMesureModal" data-id="<?= $mesure_curr['id'] ?>">
                                        <span class="uil-pen fs-9"></span>
                                    </button>
                                <?php endif; ?>

                                <?php if (checkPermis($db, 'delete', 2)) : ?>
                                    <button title="Supprimer" class="btn btn-sm btn-phoenix-danger p-2" type="button"
                                        onclick="deleteData(<?= $mesure_curr['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette mesure ?', 'mesures')">
                                        <span class="uil-trash-alt fs-9"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h4 class="fw-bold my-2"><?= $mesure_curr['name'] ?></h4>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <div class="card mb-3 rounded-1 shadow-sm border">
                                <div class="card-header bg-light dark__bg-dark rounded-0 py-3 pb-2 px-2 d-flex align-items-center">
                                    <h5 class="mb-0">Synthèse des émissions par année</h5>
                                </div>
                                <div class="card-body p-2" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($suivis_raw)) : ?>
                                        <table class="table table-sm table-hover table-striped small table-bordered border-emphasis">
                                            <thead class="bg-primary-subtle">
                                                <tr>
                                                    <th class="text-center">Année</th>
                                                    <th class="text-center">Inconditionnel</th>
                                                    <th class="text-center">Conditionnel</th>
                                                    <th class="text-center">Émission évitée</th>
                                                    <th class="text-center">% de réduction</th>
                                                    <th class="text-center">Objectif</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_wem = $total_wam = $total_emi = $total_taux = 0;
                                                foreach ($annees as $annee):
                                                    $mesure = new Mesure($db);
                                                    $emi_evitee = $mesure->getEmissionsEviteesAnnee($mesure_curr['id'], $annee);
                                                    $taux_reduc = $emi_evitee['bau'] > 0 ? (($emi_evitee['bau'] - $emi_evitee['wam']) / $emi_evitee['bau']) * 100 : 0;

                                                    $total_wem += $emi_evitee['wem'];
                                                    $total_wam += $emi_evitee['wam'];
                                                    $total_emi += $emi_evitee['wem'] + $emi_evitee['wam'];
                                                    $total_taux += $taux_reduc;
                                                ?>
                                                    <tr>
                                                        <td class="text-center fw-bold"><?= $annee ?></td>
                                                        <td class="text-center"><?= $emi_evitee['wem'] > 0 ? number_format($emi_evitee['wem'], 2) : '-' ?></td>
                                                        <td class="text-center"><?= $emi_evitee['wam'] > 0 ? number_format($emi_evitee['wam'], 2) : '-' ?></td>
                                                        <td class="text-center fw-bold"><?= number_format($emi_evitee['wem'] + $emi_evitee['wam'], 2) ?></td>
                                                        <td class="text-center fw-bold"><?= number_format($taux_reduc, 2) ?></td>
                                                        <td class="text-center">
                                                            <?php if ($emi_evitee['bau'] > 0): ?>
                                                                <?php if ($taux_reduc >= 100): ?>
                                                                    <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-success">Atteinte</span>
                                                                <?php elseif ($taux_reduc >= 80): ?>
                                                                    <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-info">Partielle</span>
                                                                <?php elseif ($taux_reduc >= 50): ?>
                                                                    <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-warning">En cours</span>
                                                                <?php else: ?>
                                                                    <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-danger">Non atteinte</span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-secondary">Pas de cible</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="bg-warning-subtle text-dark fw-bold">
                                                <tr>
                                                    <td class="text-center">Total</td>
                                                    <td class="text-center"><?= number_format($total_wem, 2) ?></td>
                                                    <td class="text-center"><?= number_format($total_wam, 2) ?></td>
                                                    <td class="text-center"><?= number_format($total_emi, 2) ?></td>
                                                    <td class="text-center"><?= number_format($total_taux, 2) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($emi_evitee['bau'] > 0): ?>
                                                            <?php if ($total_taux >= 100): ?>
                                                                <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-success">Atteinte</span>
                                                            <?php elseif ($total_taux >= 80): ?>
                                                                <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-info">Partielle</span>
                                                            <?php elseif ($total_taux >= 50): ?>
                                                                <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-warning">En cours</span>
                                                            <?php else: ?>
                                                                <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-danger">Non atteinte</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge rounded-pill py-1 badge-phoenix badge-phoenix-secondary">Pas de cible</span>
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

                            <div class="card mb-3 rounded-1 shadow-sm border">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Suivis de la mesure</h5>
                                    <?php if (checkPermis($db, 'create')) : ?>
                                        <button title="Nouvelle valeur" type="button" class="btn btn-sm btn-subtle-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#newIndicateurSuiviModal" data-unite="<?php echo $mesure_curr['unite']; ?>" data-mesure_id="<?php echo $mesure_curr['id']; ?>" data-indicateur_id="<?php echo $mesure_curr['referentiel_id']; ?>">
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
                                                        <td class="px-2 fw-bold"><?= is_numeric($suivi['valeur']) ? number_format($suivi['valeur'], 2) : $suivi['valeur'] ?></td>
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
                                            <tfoot class="bg-warning-subtle">
                                                <tr>
                                                    <td class="text-center fw-bold">Total </td>
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

                        <div class="col-lg-4">
                            <?php
                            $annee_prevision = $_GET['annee'] ?? date('Y');
                            $mesure = new Mesure($db);
                            $emiEvitee_prevision = $projections_grouped[$mesure_curr['secteur_id']][$annee_prevision] ?? [];

                            $bau_prevision = (float)($emiEvitee_prevision['bau'] ?? 0);
                            $wem_prevision = (float)($emiEvitee_prevision['wem'] ?? 0); // inconditionnel
                            $wam_prevision = (float)($emiEvitee_prevision['wam'] ?? 0); // conditionnel

                            $total_prevision = $wem_prevision + $wam_prevision;
                            $emiEvitee_mesure = $mesure->getEmissionsEviteesAnnee($mesure_curr['id'], $annee_prevision);
                            $wem_realise = (float)($emiEvitee_mesure['wem'] ?? 0);
                            $wam_realise = (float)($emiEvitee_mesure['wam'] ?? 0);

                            $total_realise = $wem_realise + $wam_realise;
                            $progression = $total_prevision > 0 ? ($total_realise / $total_prevision) * 100 : 0;
                            $taux_inconditionnel = $wem_prevision > 0 ? ($wem_realise / $wem_prevision) * 100 : 0;
                            $taux_conditionnel   = $wam_prevision > 0 ? ($wam_realise / $wam_prevision) * 100 : 0;
                            ?>

                            <div class="card mb-3 rounded-1 overflow-hidden">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 p-2">
                                    <h3 class="h6 mb-0 fw-bold">Réalisations vs Prévisions</h3>

                                    <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="m-0">
                                        <input type="hidden" name="id" value="<?= $mesure_id ?>">
                                        <select name="annee"
                                            class="badge badge-phoenix badge-phoenix-primary rounded-1"
                                            onchange="this.form.submit()">

                                            <?php for ($i = $mesure_curr['annee_debut']; $i <= $mesure_curr['annee_fin']; $i++): ?>
                                                <option value="<?= $i ?>" <?= ($annee_prevision == $i) ? 'selected' : '' ?>>
                                                    Objectif <?= $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </form>
                                </div>

                                <div class="card-body p-0">
                                    <div class="d-flex justify-content-center py-3">
                                        <div class="position-relative">
                                            <div class="gauge-conic rounded-circle"
                                                style="width:160px; height:160px; background: conic-gradient(#0d6efd 0% <?= $progression ?>%, #e9ecef <?= $progression ?>% 100%);">
                                            </div>

                                            <div class="position-absolute top-50 start-50 translate-middle bg-primary-subtle rounded-circle d-flex flex-column align-items-center justify-content-center shadow-inner"
                                                style="width:120px;height:120px;">
                                                <span class="fs-5 fw-bold text-primary"><?= round($progression) ?>%</span>
                                                <span class="text-uppercase text-muted small fw-bold">Global</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3 p-3 border-top">
                                        <div class="col-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Réalisé</p>
                                            <p class="h5 fw-bold text-primary mb-0">
                                                <?= number_format($total_realise, 0, ',', ' ') . ' ' . ($mesure_curr['unite'] ?? 'tCO₂e') ?>
                                            </p>
                                        </div>

                                        <div class="col-6 text-end">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Prévision <?= $annee_prevision ?></p>
                                            <p class="h5 fw-bold text-primary mb-0">
                                                <?= number_format($total_prevision, 0, ',', ' ') . ' ' . ($mesure_curr['unite'] ?? 'tCO₂e') ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="px-3 pb-3">
                                        <small class="text-muted">Objectif inconditionnel (WEM)</small>

                                        <div class="progress" style="height:8px;">
                                            <div class="progress-bar bg-success"
                                                style="width: <?= $taux_inconditionnel ?>%">
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-1">
                                            <small><?= number_format($wem_realise, 0, ',', ' ') ?></small>
                                            <small><?= round($taux_inconditionnel) ?>%</small>
                                            <small><?= number_format($wem_prevision, 0, ',', ' ') ?></small>
                                        </div>
                                    </div>

                                    <div class="px-3 pb-3">
                                        <small class="text-muted">Objectif conditionnel (WAM)</small>
                                        <div class="progress" style="height:8px;">
                                            <div class="progress-bar bg-warning"
                                                style="width: <?= $taux_conditionnel ?>%">
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-1">
                                            <small><?= number_format($wam_realise, 0, ',', ' ') ?></small>
                                            <small><?= round($taux_conditionnel) ?>%</small>
                                            <small><?= number_format($wam_prevision, 0, ',', ' ') ?></small>
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <div class="card mb-3 rounded-1 overflow-hidden">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 p-2">
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
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 p-2">
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
                                            <span class="text-body"><= $objectif ?></span>
                                        </li>
                                    <php endforeach; ?>
                                </ul> -->
                                    <p class="text-body mb-0"><?= $mesure_curr['objectif'] ?></p>
                                </div>
                            </div>

                            <div class="card mb-3 rounded-1">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 p-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="far fa-list-alt text-primary"></span>
                                        <h3 class="h6 mb-0 fw-bold">Informations techniques</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Période</p>
                                            <small class="fw-semibold mb-0"><?= $mesure_curr['annee_debut'] . "-" . $mesure_curr['annee_fin'] ?></small>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-uppercase text-muted small fw-bold mb-1">Gaz concernés</p>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach (explode(',', $mesure_curr['gaz']) as $gaz) : ?>
                                                    <span class="badge rounded-1" style="background-color: <?php echo $gaz_colors[strtoupper($gaz)] ?? '#6c757d'; ?>; color: white;">
                                                        <?php echo $gaz; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
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