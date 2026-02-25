<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Analyse budgétaire | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    // Secteurs
    $secteur = new Secteur($db);
    $secteurs = $secteur->read();
    $secteurs = array_filter($secteurs, function ($s) {
        return $s['parent'] == 0;
    });
    $grouped_secteur = [];
    foreach ($secteurs as $secteur) {
        $grouped_secteur[$secteur['id']] = $secteur;
    }

    // Conventions
    $convention = new Convention($db);
    $conventions = $convention->read();
    $conventions_par_secteur = [];
    $conventions_par_type = [];
    $total_conventions = 0;
    foreach ($conventions as $conv) {
        $total_conventions += floatval($conv['montant'] ?? 0);

        if ($conv['instrument'] != 0) {
            if (!isset($conventions_par_type[$conv['instrument']])) {
                $conventions_par_type[$conv['instrument']] = ['name' => listTypeFinancement()[$conv['instrument']], 'montant_total' => 0,];
            }
            $conventions_par_type[$conv['instrument']][] = $conv;
            $conventions_par_type[$conv['instrument']]['montant_total'] += floatval($conv['montant'] ?? 0);
        }

        if ($conv['secteur_id'] != 0) {
            if (!isset($conventions_par_secteur[$conv['secteur_id']])) {
                $conventions_par_secteur[$conv['secteur_id']] = ['name' => $grouped_secteur[$conv['secteur_id']]['name'], 'montant_total' => 0,];
            }
            $conventions_par_secteur[$conv['secteur_id']][] = $conv;
            $conventions_par_secteur[$conv['secteur_id']]['montant_total'] += floatval($conv['montant'] ?? 0);
        }
    }


    // Chart par Instruments
    $conventions_chart_type = [];
    foreach ($conventions_par_type as $type_id => $convention) {
        $conventions_chart_type[] = [
            'name' => listTypeFinancement()[$type_id],
            'y' => floatval($convention['montant_total'] ?? 0),
        ];
    }

    // Chart par Secteurs
    $conventions_chart_secteur = [];
    foreach ($conventions_par_secteur as $secteur_id => $convention) {
        $conventions_chart_secteur[] = [
            'name' => $grouped_secteur[$secteur_id]['name'],
            'y' => floatval($convention['montant_total'] ?? 0),
        ];
    }

    // Partenaires/Bailleurs
    $partenaire = new Partenaire($db);
    $partenaires = $partenaire->read();
    $grouped_partenaire = [];
    foreach ($partenaires as $part) {
        $grouped_partenaire[$part['id']] = $part;
    }

    // Préparation données pour graphiques par bailleur
    $data_bailleur = [];
    $data_montant = [];
    $conventions_par_bailleur = [];
    $conventions_chart_bailleur = [];

    foreach ($conventions as $conv) {
        $bailleur_id = $conv['partenaire_id'];
        $bailleur_sigle = $grouped_partenaire[$bailleur_id]['sigle'] ?? 'Non défini';
        $data_bailleur[] = $bailleur_sigle;
        $data_montant[] = floatval($conv['montant'] ?? 0);

        if (!isset($conventions_par_bailleur[$bailleur_id])) {
            $conventions_par_bailleur[$bailleur_id] = [
                'name' => $bailleur_sigle,
                'montant_total' => 0,
                'conventions' => []
            ];
        }
        $conventions_par_bailleur[$bailleur_id]['montant_total'] += floatval($conv['montant'] ?? 0);
        $conventions_par_bailleur[$bailleur_id]['conventions'][] = $conv;
    }

    foreach ($conventions_par_bailleur as $bailleur) {
        $conventions_chart_bailleur[] = [
            'name' => $bailleur['name'],
            'y' => $bailleur['montant_total'],
        ];
    }

    // Projets
    $projet = new Projet($db);
    $projets = $projet->read();
    $projets_actifs = array_filter($projets, function ($p) {
        return ($p['state'] ?? '') == 'actif';
    });

    // Tâches
    $tache = new Tache($db);
    $taches = $tache->read();
    $taches_actives = array_filter($taches, function ($t) {
        return ($t['state'] ?? '') == 'actif';
    });

    // Coûts des tâches (décaissements)
    $tache_cout = new TacheCout($db);
    $tache_couts = $tache_cout->read();
    $grouped_tache_couts = [];
    $total_decaisse = 0;

    foreach ($tache_couts as $cout) {
        $grouped_tache_couts[$cout['tache_id']][] = $cout;
        $total_decaisse += floatval($cout['montant'] ?? 0);
    }

    // Calcul des décaissements par convention
    $decaisse_par_convention = [];
    foreach ($tache_couts as $cout) {
        $conv_id = $cout['convention_id'] ?? null;
        if ($conv_id) {
            if (!isset($decaisse_par_convention[$conv_id])) {
                $decaisse_par_convention[$conv_id] = 0;
            }
            $decaisse_par_convention[$conv_id] += floatval($cout['montant'] ?? 0);
        }
    }

    // Données pour graphique timeline
    $timeline_data = [];
    $timeline_labels = [];
    foreach ($tache_couts as $cout) {
        if (!empty($cout['created_at'])) {
            $mois = date('Y-m', strtotime($cout['created_at']));
            if (!isset($timeline_data[$mois])) {
                $timeline_data[$mois] = 0;
            }
            $timeline_data[$mois] += floatval($cout['montant'] ?? 0);
        }
    }
    ksort($timeline_data);
    $timeline_labels = array_keys($timeline_data);
    $timeline_values = array_values($timeline_data);

    // Statistiques globales
    $taux_execution_global = $total_conventions > 0 ? round(($total_decaisse / $total_conventions) * 100, 1) : 0;
    $nb_conventions = count($conventions);
    $nb_bailleurs = count($conventions_par_bailleur);
    $nb_projets_avec_financement = count(array_filter($projets_actifs, function ($p) use ($conventions) {
        foreach ($conventions as $c) {
            if ($c['projet_id'] == $p['id']) return true;
        }
        return false;
    }));
    ?>
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content">
            <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                <div class="card-body py-1 px-3 d-lg-flex flex-row justify-content-between align-items-center">
                    <div class="col-lg-4">
                        <h5 class="my-1 fw-black fs-8">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                            Analyse budgétaire
                        </h5>
                        <p class="text-muted mb-0 fs-9">Suivi financier et analyse des décaissements</p>
                    </div>
                    <div class="col-lg-auto">
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary-subtle text-primary p-2">
                                <i class="fas fa-file-contract me-1"></i>
                                <?php echo $nb_conventions; ?> conventions
                            </span>
                            <span class="badge bg-success-subtle text-success p-2">
                                <i class="fas fa-coins me-1"></i>
                                <?php echo number_format($total_conventions, 0, ',', ' '); ?> USD
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3 mx-n5 mt-1">
                <div class="col-sm-6 col-xl-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="fas fa-file-invoice fs-3 text-primary"></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Conventions</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo $nb_conventions; ?></h3>
                                    <small class="text-muted"><?php echo $nb_bailleurs; ?> bailleurs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="fas fa-coins fs-3 text-warning"></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Budget total</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo number_format($total_conventions, 0, ',', ' '); ?></h3>
                                    <small class="text-muted">USD</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="fas fa-hand-holding-usd fs-3 text-success"></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Décaissé</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo number_format($total_decaisse, 0, ',', ' '); ?></h3>
                                    <small class="text-muted">USD</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="fas fa-percent fs-3 text-info"></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Taux exécution</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo $taux_execution_global; ?>%</h3>
                                    <div class="progress progress-sm mt-2" style="width: 100px;">
                                        <div class="progress-bar bg-<?php echo $taux_execution_global >= 80 ? 'success' : ($taux_execution_global >= 50 ? 'warning' : 'danger'); ?>"
                                            style="width: <?php echo $taux_execution_global; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-n4 px-3 mx-lg-n6 bg-body-emphasis border-top">
                <div class="card-body d-lg-flex flex-row justify-content-between align-items-center g-3">
                    <ul class="nav nav-underline fs-9" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active"
                                id="decaisse-tab" data-bs-toggle="tab" href="#tab-decaisse" role="tab" aria-controls="decaisse-tab" aria-selected="true">
                                <i class="fas fa-chart-line me-1"></i> Évolution des décaissements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                                id="actor-tab" data-bs-toggle="tab" href="#tab-actor" role="tab" aria-controls="actor-tab" aria-selected="true">
                                <i class="fas fa-building me-1"></i> Analyse par bailleur
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                                id="activity-tab" data-bs-toggle="tab" href="#tab-activity" role="tab" aria-controls="activity-tab" aria-selected="true">
                                <i class="fas fa-tasks me-1"></i> Analyse par activité
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade active show" id="tab-decaisse" role="tabpanel" aria-labelledby="decaisse-tab">
                    <?php include './components/tabs/tab_finance_decaisse.php'; ?>
                </div>

                <div class="tab-pane fade" id="tab-actor" role="tabpanel" aria-labelledby="actor-tab">
                    <?php include './components/tabs/tab_finance_actor.php'; ?>
                </div>

                <div class="tab-pane fade" id="tab-activity" role="tabpanel" aria-labelledby="activity-tab">
                    <?php include './components/tabs/tab_finance_activity.php'; ?>
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

<script>
    mrvDonutChart({
        id: 'chartFinanceConvention',
        title: 'Financements par convention',
        unite: 'USD',
        data: <?= json_encode($conventions_chart_type ?? []) ?>,
    });

    mrvDonutChart({
        id: 'chartFinanceBailleur',
        title: 'Financements par bailleurs',
        unite: 'USD',
        data: <?= json_encode($conventions_chart_bailleur ?? []) ?>,
    });

    mrvDonutChart({
        id: 'chartFinanceProjet',
        title: 'Financements par projet',
        unite: 'USD',
        data: <?= json_encode($budget_chart_projet ?? []) ?>,
    });

    mrvTimelineChart({
        id: 'timelineMultiChart',
        labels: <?= json_encode($timeline_labels); ?>,
        series: [{
            name: 'Décaissements mensuelles',
            data: <?php echo json_encode($timeline_values ?? []); ?>,
            color: '#ffc107',
        }],
        // title: 'Comparaison réel vs prévisions',
        // series: [{
        //         name: 'Décaissements prévisionnels',
        //         data: <?php echo json_encode($timeline_values ?? []); ?>,
        //         color: '#ffc107',
        //         dashStyle: 'dash'
        //     },
        //     {
        //         name: 'Décaissements réels',
        //         data: <?= json_encode($timeline_values_reel ?? []); ?>,
        //         color: '#0d6efd'
        //     },
        // ],
        showLegend: true
    });
</script>

</html>