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

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'decaisse';
    if (!in_array($tab, ['decaisse', 'actor', 'activity'])) $tab = 'decaisse';

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
                        <li class="nav-item" role="tab">
                            <a class="nav-link <?php echo $tab == 'decaisse' ? 'active' : ''; ?>"
                                id="decaisse-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=decaisse'; ?>">
                                <i class="fas fa-chart-line me-1"></i> Évolution des décaissements
                            </a>
                        </li>
                        <li class="nav-item" role="tab">
                            <a class="nav-link <?php echo $tab == 'actor' ? 'active' : ''; ?>"
                                id="actor-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=actor'; ?>">
                                <i class="fas fa-building me-1"></i> Analyse par bailleur
                            </a>
                        </li>
                        <li class="nav-item" role="tab">
                            <a class="nav-link <?php echo $tab == 'activity' ? 'active' : ''; ?>"
                                id="activity-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=activity'; ?>">
                                <i class="fas fa-tasks me-1"></i> Analyse par activité
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade <?php echo $tab == 'decaisse' ? 'active show' : ''; ?>" id="tab-decaisse" role="tabpanel" aria-labelledby="decaisse-tab">
                    <div class="mx-n4 p-3 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="card shadow-sm rounded-1 h-100">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-line me-2 text-primary"></i>
                                            Évolution mensuelle des décaissements
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($timeline_labels)): ?>
                                            <div class="chart-container">
                                                <div class="card-body p-2" id="timelineMultiChart" style="min-height: 350px;"></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune donnée de décaissement disponible</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card shadow-sm rounded-1 h-100">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                                            Répartition des financements par convention
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($conventions)): ?>
                                            <div class="chart-container">
                                                <div class="card-body p-2" id="chartFinanceConvention" style="min-height: 350px;"></div>
                                            </div>
                                            <div class="mt-3 border-top">
                                                <h6 class="text-muted m-3">Détail par convention</h6>
                                                <div class="px-3" style="max-height: 300px; overflow-y: auto;">
                                                    <?php
                                                    $top_conventions = array_slice($conventions, 0, 5);
                                                    foreach ($top_conventions as $index => $conv):
                                                        $pourcentage = $total_conventions > 0 ? round(($conv['montant'] / $total_conventions) * 100, 1) : 0;
                                                    ?>
                                                        <div class="d-flex align-items-center justify-content-between fs-9 mb-2">
                                                            <span class="dot p-1 bg-<?php echo listCouleur()[$index % count(listCouleur())]; ?> me-2"></span>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between">
                                                                    <?php echo $conv['name']; ?>
                                                                    <span class="fw-semibold"><?php echo $pourcentage; ?>%</span>
                                                                </div>
                                                                <small class="text-muted"><?php echo $conv['montant']; ?> USD</small>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune convention</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'actor' ? 'active show' : ''; ?>" id="tab-actor" role="tabpanel" aria-labelledby="actor-tab">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="row mx-0 g-3">
                            <div class="col-lg-7">
                                <div class="card shadow-sm rounded-1">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-table me-2 text-primary"></i>
                                            Détail des conventions par bailleur
                                        </h5>
                                        <span class="badge bg-primary"><?php echo count($conventions); ?> conventions</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive" style="min-height: 432px;">
                                            <table class="table small table-bordered fs-9 table-hover mb-0" id="id-datatable1">
                                                <thead class="bg-primary-subtle">
                                                    <tr>
                                                        <th class="align-middle">Code</th>
                                                        <th class="align-middle">Convention</th>
                                                        <th class="align-middle">Bailleur</th>
                                                        <th class="align-middle text-end text-nowrap">Prévu (USD)</th>
                                                        <th class="align-middle text-end text-nowrap">Décaissé (USD)</th>
                                                        <th class="align-middle text-center">Taux</th>
                                                        <th class="align-middle text-center">Statut</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list">
                                                    <?php foreach ($conventions as $convention):
                                                        $montant_prev = floatval($convention['montant'] ?? 0);
                                                        $montant_decaisse = $decaisse_par_convention[$convention['id']] ?? 0;
                                                        $taux = $montant_prev > 0 ? round(($montant_decaisse / $montant_prev) * 100, 1) : 0;

                                                        $now = time();
                                                        $date_fin = strtotime($convention['date_fin'] ?? '');
                                                        if ($date_fin && $now > $date_fin) {
                                                            $statut = 'Expirée';
                                                            $statut_class = 'danger';
                                                        } elseif ($taux >= 100) {
                                                            $statut = 'Soldée';
                                                            $statut_class = 'success';
                                                        } elseif ($taux > 0) {
                                                            $statut = 'En cours';
                                                            $statut_class = 'warning';
                                                        } else {
                                                            $statut = 'Non démarrée';
                                                            $statut_class = 'secondary';
                                                        }
                                                    ?>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <span class="fw-semibold"><?php echo htmlspecialchars($convention['code'] ?? ''); ?></span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <span title="<?php echo htmlspecialchars($convention['name'] ?? ''); ?>">
                                                                    <?php echo htmlspecialchars($convention['name'] ?? ''); ?>
                                                                </span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <?php echo $grouped_partenaire[$convention['partenaire_id']]['sigle'] ?? 'N/A'; ?>
                                                            </td>
                                                            <td class="align-middle text-end">
                                                                <span class="fw-semibold"><?php echo number_format($montant_prev, 0, ',', ' '); ?></span>
                                                            </td>
                                                            <td class="align-middle text-end">
                                                                <span class="text-success"><?php echo number_format($montant_decaisse, 0, ',', ' '); ?></span>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $taux >= 80 ? 'success' : ($taux >= 50 ? 'warning' : 'light'); ?> text-<?php echo $taux >= 80 ? 'white' : ($taux >= 50 ? 'dark' : 'secondary'); ?>">
                                                                    <?php echo $taux; ?>%
                                                                </span>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $statut_class; ?>">
                                                                    <?php echo $statut; ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="bg-light">
                                                    <tr class="text-nowrap">
                                                        <th colspan="3" class="text-end">TOTAUX</th>
                                                        <th class="text-end"><?php echo number_format($total_conventions, 0, ',', ' '); ?></th>
                                                        <th class="text-end"><?php echo number_format($total_decaisse, 0, ',', ' '); ?></th>
                                                        <th class="text-center"><?php echo $taux_execution_global; ?>%</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card shadow-sm rounded-1 h-100">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                                            Répartition des financements par bailleur
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($conventions_par_bailleur)): ?>
                                            <div class="chart-container">
                                                <div class="card-body p-2" id="chartFinanceBailleur" style="min-height: 350px;"></div>
                                            </div>

                                            <div class="mt-3 border-top">
                                                <h6 class="text-muted m-3">Détail par bailleur</h6>
                                                <div class="px-3" style="max-height: 300px; overflow-y: auto;">
                                                    <?php
                                                    $i = 0;
                                                    foreach ($conventions_par_bailleur as $bailleur_id => $data):
                                                        $pourcentage = $total_conventions > 0 ? round(($data['montant_total'] / $total_conventions) * 100, 1) : 0;
                                                    ?>
                                                        <div class="d-flex align-items-center fs-9 mb-2">
                                                            <span class="dot p-1 bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?> me-2"></span>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="fw-semibold"><?php echo $data['name']; ?></span>
                                                                    <span class="text-muted"><?php echo number_format($data['montant_total'], 0, ',', ' '); ?> USD</span>
                                                                </div>
                                                                <div class="progress progress-sm mt-1">
                                                                    <div class="progress-bar bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?>"
                                                                        style="width: <?php echo $pourcentage; ?>%"></div>
                                                                </div>
                                                                <small class="text-muted"><?php echo count($data['conventions']); ?> conventions • <?php echo $pourcentage; ?>%</small>
                                                            </div>
                                                        </div>
                                                    <?php
                                                        $i++;
                                                    endforeach;
                                                    ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-5 my-3">
                                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune donnée disponible</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'activity' ? 'active show' : ''; ?>" id="tab-activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="row mx-0 g-3">
                            <div class="col-lg-7">
                                <div class="card shadow-sm rounded-1">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-tasks me-2 text-primary"></i>
                                            Budget des activités
                                        </h5>
                                        <span class="badge bg-primary"><?php echo count($taches_actives); ?> activités</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive" style="min-height: 432px;">
                                            <table class="table small table-bordered fs-9 table-hover mb-0" id="id-datatable2">
                                                <thead class="bg-primary-subtle">
                                                    <tr>
                                                        <th class="align-middle">Code</th>
                                                        <th class="align-middle">Activité</th>
                                                        <th class="align-middle">Projet</th>
                                                        <th class="align-middle text-end text-nowrap">Prévu (USD)</th>
                                                        <th class="align-middle text-end text-nowrap">Décaissé (USD)</th>
                                                        <th class="align-middle text-center">Exécution</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list">
                                                    <?php
                                                    $total_prev_activites = 0;
                                                    $total_decaisse_activites = 0;

                                                    foreach ($taches_actives as $tache):
                                                        $montant_prev = isset($grouped_tache_couts[$tache['id']]) ? array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant')) : 0;
                                                        $montant_decaisse = $montant_prev;

                                                        $total_prev_activites += $montant_prev;
                                                        $total_decaisse_activites += $montant_decaisse;
                                                        $taux_activite = $montant_prev > 0 ? round(($montant_decaisse / $montant_prev) * 100, 1) : 0;

                                                        $projet_associe = array_filter($projets_actifs, function ($p) use ($tache) {
                                                            return $p['id'] == $tache['projet_id'];
                                                        });
                                                        $projet_nom = !empty($projet_associe) ? reset($projet_associe)['code'] : 'N/A';
                                                    ?>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <span class="fw-semibold"><?php echo htmlspecialchars($tache['code'] ?? ''); ?></span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <span title="<?php echo htmlspecialchars($tache['name'] ?? ''); ?>">
                                                                    <?php echo htmlspecialchars($tache['name'] ?? ''); ?>
                                                                </span>
                                                            </td>
                                                            <td class="align-middle"><?php echo $projet_nom; ?></td>
                                                            <td class="align-middle text-end">
                                                                <?php if ($montant_prev > 0): ?>
                                                                    <span class="fw-semibold"><?php echo number_format($montant_prev, 0, ',', ' '); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="align-middle text-end">
                                                                <?php if ($montant_decaisse > 0): ?>
                                                                    <span class="text-success"><?php echo number_format($montant_decaisse, 0, ',', ' '); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <?php if ($montant_prev > 0): ?>
                                                                    <span class="ms-2 fw-semibold"><?php echo $taux_activite; ?>%</span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="bg-light">
                                                    <tr class="text-nowrap">
                                                        <th colspan="3" class="text-end">TOTAUX</th>
                                                        <th class="text-end"><?php echo number_format($total_prev_activites, 0, ',', ' '); ?></th>
                                                        <th class="text-end"><?php echo number_format($total_decaisse_activites, 0, ',', ' '); ?></th>
                                                        <th class="text-center">
                                                            <?php
                                                            $taux_global_activites = $total_prev_activites > 0 ? round(($total_decaisse_activites / $total_prev_activites) * 100, 1) : 0;
                                                            ?>
                                                            <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $taux_global_activites >= 80 ? 'success' : ($taux_global_activites >= 50 ? 'warning' : 'danger'); ?>">
                                                                <?php echo $taux_global_activites; ?>%
                                                            </span>
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card shadow-sm rounded-1 h-100">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                                            Répartition par projet
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php
                                        $budget_par_projet = [];
                                        $budget_chart_projet = [];
                                        foreach ($projets_actifs as $proj) {
                                            $budget_par_projet[$proj['id']] = [
                                                'code' => $proj['code'],
                                                'name' => $proj['code'] . ' - ' . $proj['name'],
                                                'montant' => 0
                                            ];
                                        }

                                        foreach ($taches_actives as $tache) {
                                            $projet_id = $tache['projet_id'];
                                            if (isset($grouped_tache_couts[$tache['id']])) {
                                                $montant = array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant'));
                                                if (isset($budget_par_projet[$projet_id])) {
                                                    $budget_par_projet[$projet_id]['montant'] += $montant;
                                                }
                                            }
                                        }

                                        $budget_par_projet = array_filter($budget_par_projet, function ($p) {
                                            return $p['montant'] > 0;
                                        });

                                        foreach ($budget_par_projet as $projet) {
                                            $budget_chart_projet[] = [
                                                'name' => $projet['code'],
                                                'y' => $projet['montant'],
                                            ];
                                        }
                                        ?>

                                        <?php if (!empty($budget_par_projet)): ?>
                                            <div class="chart-container">
                                                <div class="card-body p-2" id="chartFinanceProjet" style="min-height: 350px;"></div>
                                            </div>

                                            <div class="mt-3 border-top">
                                                <h6 class="text-muted m-3">Budget par projet</h6>

                                                <div class="px-3" style="max-height: 300px; overflow-y: auto;">
                                                    <?php
                                                    $total_budget_projets = array_sum(array_column($budget_par_projet, 'montant'));
                                                    $i = 0;
                                                    foreach ($budget_par_projet as $projet):
                                                        $pourcentage = $total_budget_projets > 0 ? round(($projet['montant'] / $total_budget_projets) * 100, 1) : 0;
                                                    ?>
                                                        <div class="d-flex align-items-center fs-9 mb-2">
                                                            <span class="dot p-1 bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?> me-2"></span>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between small">
                                                                    <span class="text-truncate" style="max-width: 300px;"><?php echo html_entity_decode($projet['name']); ?></span>
                                                                    <span class="fw-semibold"><?php echo number_format($projet['montant'], 0, ',', ' '); ?> USD</span>
                                                                </div>
                                                                <div class="progress progress-sm my-1">
                                                                    <div class="progress-bar bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?>"
                                                                        style="width: <?php echo $pourcentage; ?>%"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php
                                                        $i++;
                                                    endforeach;
                                                    ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Aucune donnée budgétaire par projet</p>
                                            </div>
                                        <?php endif; ?>
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