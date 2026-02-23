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

    // Conventions
    $convention = new Convention($db);
    $conventions = $convention->read();
    $grouped_conventions = [];
    $total_conventions = 0;
    foreach ($conventions as $conv) {
        $grouped_conventions[$conv['id']] = $conv;
        $total_conventions += floatval($conv['montant'] ?? 0);
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

    foreach ($conventions as $conv) {
        $bailleur_id = $conv['partenaire_id'];
        $bailleur_sigle = $grouped_partenaire[$bailleur_id]['sigle'] ?? 'Non défini';
        $data_bailleur[] = $bailleur_sigle;
        $data_montant[] = floatval($conv['montant'] ?? 0);

        // Agrégation par bailleur
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
                                <i class="fas fa-hand-holding-usd me-1"></i>
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
                            <div class="col-lg-8">
                                <div class="card shadow-sm rounded-1 h-100">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-line me-2 text-primary"></i>
                                            Évolution mensuelle des décaissements
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($timeline_labels)): ?>
                                            <div class="chart-container">
                                                <canvas id="timelineChart"></canvas>
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
                            <div class="col-lg-4">
                                <div class="card shadow-sm rounded-1 h-100">
                                    <div class="card-header bg-light py-2">
                                        <h5 class="mb-0">
                                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                                            Répartition par convention
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($conventions)): ?>
                                            <div class="chart-container" style="min-height: 250px;">
                                                <canvas id="conventionPieChart"></canvas>
                                            </div>
                                            <div class="mt-3 small">
                                                <?php
                                                $top_conventions = array_slice($conventions, 0, 5);
                                                foreach ($top_conventions as $index => $conv):
                                                    $pourcentage = $total_conventions > 0 ? round(($conv['montant'] / $total_conventions) * 100, 1) : 0;
                                                    $couleurs = ['primary', 'success', 'info', 'warning', 'danger'];
                                                ?>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span>
                                                            <span class="dot bg-<?php echo $couleurs[$index % count($couleurs)]; ?> me-2"></span>
                                                            <?php echo $conv['name']; ?>
                                                        </span>
                                                        <span class="fw-semibold"><?php echo $pourcentage; ?>%</span>
                                                    </div>
                                                <?php endforeach; ?>
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
                                            <table class="table table-bordered fs-9 table-hover mb-0" id="conventions-datatable">
                                                <thead class="bg-primary-subtle">
                                                    <tr>
                                                        <th class="align-middle">Code</th>
                                                        <th class="align-middle">Convention</th>
                                                        <th class="align-middle">Bailleur</th>
                                                        <th class="align-middle text-end">Montant prévu</th>
                                                        <th class="align-middle text-end">Décaissé</th>
                                                        <th class="align-middle text-center">Taux</th>
                                                        <th class="align-middle text-center">Statut</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list">
                                                    <?php foreach ($conventions as $convention):
                                                        $montant_prev = floatval($convention['montant'] ?? 0);
                                                        $montant_decaisse = $decaisse_par_convention[$convention['id']] ?? 0;
                                                        $taux = $montant_prev > 0 ? round(($montant_decaisse / $montant_prev) * 100, 1) : 0;

                                                        // Déterminer le statut
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
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                                    title="<?php echo htmlspecialchars($convention['name'] ?? ''); ?>">
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
                                                    <tr>
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
                                    <div class="card-body">
                                        <?php if (!empty($conventions_par_bailleur)): ?>
                                            <div class="chart-container" style="min-height: 350px;">
                                                <canvas id="bailleurChart"></canvas>
                                            </div>

                                            <!-- Légende détaillée -->
                                            <div class="mt-4">
                                                <h6 class="text-muted mb-3">Détail par bailleur</h6>
                                                <?php
                                                $couleurs_bailleurs = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                                                $i = 0;
                                                foreach ($conventions_par_bailleur as $bailleur_id => $data):
                                                    $pourcentage = $total_conventions > 0 ? round(($data['montant_total'] / $total_conventions) * 100, 1) : 0;
                                                ?>
                                                    <div class="d-flex align-items-center mb-3">
                                                        <span class="dot bg-<?php echo $couleurs_bailleurs[$i % count($couleurs_bailleurs)]; ?> me-2"></span>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between">
                                                                <span class="fw-semibold"><?php echo $data['name']; ?></span>
                                                                <span class="text-muted"><?php echo number_format($data['montant_total'], 0, ',', ' '); ?> USD</span>
                                                            </div>
                                                            <div class="progress progress-sm mt-1">
                                                                <div class="progress-bar bg-<?php echo $couleurs_bailleurs[$i % count($couleurs_bailleurs)]; ?>"
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
                                            <table class="table table-bordered fs-9 table-hover mb-0" id="activites-datatable">
                                                <thead class="bg-primary-subtle">
                                                    <tr>
                                                        <th class="align-middle">Code</th>
                                                        <th class="align-middle">Activité</th>
                                                        <th class="align-middle">Projet</th>
                                                        <th class="align-middle text-end">Montant prévu</th>
                                                        <th class="align-middle text-end">Décaissé</th>
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

                                                        // Récupérer le projet associé
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
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                                    title="<?php echo htmlspecialchars($tache['name'] ?? ''); ?>">
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
                                                    <tr>
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
                                    <div class="card-body">
                                        <?php
                                        $budget_par_projet = [];
                                        foreach ($projets_actifs as $proj) {
                                            $budget_par_projet[$proj['id']] = [
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
                                        ?>

                                        <?php if (!empty($budget_par_projet)): ?>
                                            <div class="chart-container" style="min-height: 300px;">
                                                <canvas id="projetChart"></canvas>
                                            </div>

                                            <div class="mt-4">
                                                <h6 class="text-muted mb-3">Budget par projet</h6>
                                                <?php
                                                $total_budget_projets = array_sum(array_column($budget_par_projet, 'montant'));
                                                $couleurs_projets = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                                                $i = 0;
                                                foreach ($budget_par_projet as $projet):
                                                    $pourcentage = $total_budget_projets > 0 ? round(($projet['montant'] / $total_budget_projets) * 100, 1) : 0;
                                                ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="dot bg-<?php echo $couleurs_projets[$i % count($couleurs_projets)]; ?> me-2"></span>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between small">
                                                                <span class="text-truncate" style="max-width: 150px;"><?php echo $projet['name']; ?></span>
                                                                <span class="fw-semibold"><?php echo number_format($projet['montant'], 0, ',', ' '); ?> USD</span>
                                                            </div>
                                                            <div class="progress progress-sm mt-1">
                                                                <div class="progress-bar bg-<?php echo $couleurs_projets[$i % count($couleurs_projets)]; ?>"
                                                                    style="width: <?php echo $pourcentage; ?>%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php
                                                    $i++;
                                                endforeach;
                                                ?>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif";
        Chart.defaults.font.size = 11;

        // ============================================
        // GRAPHIQUE 1 : Timeline des décaissements
        // ============================================
        const timelineCtx = document.getElementById('timelineChart');
        if (timelineCtx) {
            const labels = <?php echo json_encode($timeline_labels); ?>;
            const values = <?php echo json_encode($timeline_values); ?>;

            if (labels.length > 0) {
                const formattedLabels = labels.map(date => {
                    const [year, month] = date.split('-');
                    const monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                    return monthNames[parseInt(month) - 1] + ' ' + year;
                });

                new Chart(timelineCtx, {
                    type: 'line',
                    data: {
                        labels: formattedLabels,
                        datasets: [{
                            label: 'Décaissements mensuels (USD)',
                            data: values,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#0d6efd',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Montant: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('fr-FR').format(value) + ' USD';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // ============================================
        // GRAPHIQUE 2 : Camembert des conventions
        // ============================================
        const pieCtx = document.getElementById('conventionPieChart');
        if (pieCtx) {
            const conventions = <?php echo json_encode($conventions); ?>;
            if (conventions.length > 0) {
                const labels = conventions.map(c => c.name || 'N/A');
                const data = conventions.map(c => parseFloat(c.montant) || 0);
                const colors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#6610f2'];

                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors.slice(0, data.length),
                            borderWidth: 1,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(value) + ' USD (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        }

        // ============================================
        // GRAPHIQUE 3 : Barres par bailleur
        // ============================================
        const bailleurCtx = document.getElementById('bailleurChart');
        if (bailleurCtx) {
            const bailleurs = <?php echo json_encode($conventions_par_bailleur); ?>;
            const total = <?php echo $total_conventions; ?>;

            if (Object.keys(bailleurs).length > 0) {
                const labels = Object.values(bailleurs).map(b => b.name);
                const data = Object.values(bailleurs).map(b => b.montant_total);
                const colors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d'];

                new Chart(bailleurCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Montant (USD)',
                            data: data,
                            backgroundColor: colors.slice(0, data.length),
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return context.raw + ' USD (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('fr-FR').format(value) + ' USD';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // ============================================
        // GRAPHIQUE 4 : Répartition par projet
        // ============================================
        const projetCtx = document.getElementById('projetChart');
        if (projetCtx) {
            const projets = <?php echo json_encode(array_values($budget_par_projet)); ?>;
            const totalProjets = <?php echo array_sum(array_column($budget_par_projet, 'montant')); ?>;

            if (projets.length > 0) {
                const labels = projets.map(p => {
                    return p.name.length > 20 ? p.name.substring(0, 18) + '...' : p.name;
                });
                const data = projets.map(p => p.montant);
                const colors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#6610f2'];

                new Chart(projetCtx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors.slice(0, data.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const percentage = totalProjets > 0 ? ((value / totalProjets) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(value) + ' USD (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    });
</script>

</html>