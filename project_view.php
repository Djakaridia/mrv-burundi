<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Détails du projet | MRV - Burundi</title>

  <?php
  $project_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
  $tab = isset($_GET['tab']) ? $_GET['tab'] : 'view';

  if (!in_array($tab, ['view', 'task', 'indicator', 'finance', 'synthese'])) {
    $tab = 'view';
  }

  if ($project_id <= 0) {
    header("Location: projects.php");
    exit();
  }

  include './components/navbar & footer/head.php';

  $projet = new Projet($db);
  $projets = $projet->read();
  $projets = array_filter($projets, function ($projet) {
    return $projet['state'] == 'actif';
  });

  $conventions_par_projet = [];
  $couts_par_tache = [];
  if (!empty($projets)) {
    $convention = new Convention($db);
    $tache_cout = new TacheCout($db);
    $tache_couts = $tache_cout->read();

    foreach ($tache_couts as $cout) {
      $couts_par_tache[$cout['tache_id']][] = $cout;
    }

    foreach ($projets as $projet) {
      $convention->projet_id = $projet['id'];
      $conventions_par_projet[$projet['id']] = $convention->readByProjet();
    }
  }

  $programme = new Programme($db);
  $programmes = $programme->read();
  $programmes = array_filter($programmes, function ($programme) {
    return $programme['state'] == 'actif';
  });

  $unite = new Unite($db);
  $unites = $unite->read();

  $gaz = new Gaz($db);
  $gazs = $gaz->read();

  $user = new User($db);
  $users = $user->read();

  $grouped_users = [];
  foreach ($users as $user) {
    $grouped_users[$user['id']] = $user;
  }

  $users = array_filter($users, function ($user) {
    return $user['state'] == 'actif';
  });

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) {
    return $structure['state'] == 'actif';
  });

  $partenaire = new Partenaire($db);
  $partenaires = $partenaire->read();

  $secteur = new Secteur($db);
  $data_secteurs = $secteur->read();
  $secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
  });
  $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] > 0 && $secteur['state'] == 'actif';
  });

  $groupe_travail = new GroupeTravail($db);
  $groupes_travail = $groupe_travail->read();

  //=============================//
  // Données du projet courant
  //=============================//
  $project = new Projet($db);
  $project->id = $project_id;
  $project_curr = $project->readById();

  if (empty($project_curr)) {
    header("Location: projects.php");
    exit();
  }

  $projet_gaz = explode(',', $project_curr['gaz'] ?? "");

  $tache = new Tache($db);
  $tache->projet_id = $project_id;
  $taches_project = $tache->readByProjet();

  $tache_indicateur = new TacheIndicateur($db);
  $tache_indicateurs = $tache_indicateur->read();
  $grouped_tache_indicateurs = [];
  foreach ($tache_indicateurs as $tache_indicateur) {
    $grouped_tache_indicateurs[$tache_indicateur['tache_id']][] = $tache_indicateur;
  }

  $tache_suivi_indicateur = new TacheSuiviIndicateur($db);
  $tache_suivi_indicateurs = $tache_suivi_indicateur->read();
  $grouped_tache_suivi_indicateurs = [];
  foreach ($tache_suivi_indicateurs as $tache_suivi_indicateur) {
    $grouped_tache_suivi_indicateurs[$tache_suivi_indicateur['tache_id']][] = $tache_suivi_indicateur;
  }

  $tache_cout = new TacheCout($db);
  $tache_couts = $tache_cout->read();
  $grouped_tache_couts = [];
  foreach ($tache_couts as $tache_cout) {
    $grouped_tache_couts[$tache_cout['tache_id']][] = $tache_cout;
  }

  $conventions_projet = $conventions_par_projet[$project_curr['id']] ?? [];
  $budget_conventions = array_sum(array_column($conventions_projet, 'montant'));
  $taches_actives = array_filter($taches_project, function ($tache) {
    return $tache['state'] == 'actif';
  });

  $totalTacheCount = count($taches_actives);
  $finishedTacheCount = count(array_filter($taches_actives, function ($tache) {
    return strtolower($tache['status'] ?? '') === 'realise' || strtolower($tache['status'] ?? '') === 'terminée';
  }));

  $exe_physique = $totalTacheCount > 0 ? round(($finishedTacheCount / $totalTacheCount) * 100, 1) : 0;

  $montant_total_depense = 0;
  $decaisssements_par_mois = [];

  foreach ($taches_actives as $tache) {
    if (isset($couts_par_tache[$tache['id']])) {
      foreach ($couts_par_tache[$tache['id']] as $cout) {
        $montant = floatval($cout['montant'] ?? 0);
        $montant_total_depense += $montant;

        // Agrégation par mois pour le graphique de décaissement
        if (!empty($cout['date_decaissement'])) {
          $mois = date('Y-m', strtotime($cout['date_decaissement']));
          if (!isset($decaisssements_par_mois[$mois])) {
            $decaisssements_par_mois[$mois] = 0;
          }
          $decaisssements_par_mois[$mois] += $montant;
        }
      }
    }
  }
  ksort($decaisssements_par_mois);

  $column_categories = array_keys($decaisssements_par_mois);
  $column_data = array_values($decaisssements_par_mois);
  $budget_reference = $budget_conventions > 0 ? $budget_conventions : ($project_curr['budget'] ?? 0);
  $exe_financiere = $budget_reference > 0 ? round(($montant_total_depense / $budget_reference) * 100, 1) : 0;

  $referentiel = new Referentiel($db);
  $referentiels = $referentiel->read();
  $referentiels = array_filter($referentiels, function ($referentiel) {
    return $referentiel['state'] == 'actif';
  });

  $mesure = new Mesure($db);
  $mesures = $mesure->read();

  $niveau_resultat = new NiveauResultat($db);
  $niveau_resultats = $niveau_resultat->read();

  $indicateur = new Indicateur($db);
  $indicateur->projet_id = $project_id;
  $indicateurs_project = $indicateur->readByProjet();

  $convention = new Convention($db);
  $convention->projet_id = $project_id;
  $conventions_project = $convention->readByProjet();
  $arr_conventIds = array_column($conventions_project, 'partenaire_id');

  $partenaires_project = [];
  foreach ($partenaires as $partenaire) {
    if (in_array($partenaire['id'], $arr_conventIds)) {
      array_push($partenaires_project, $partenaire);
    }
  }

  $secteurs_project = array_filter($secteurs, function ($s) use ($project_curr) {
    return $project_curr['secteur_id'] == $s['id'];
  });

  $groupes_travail_project = array_filter($groupes_travail, function ($g) use ($project_curr) {
    $groupes_ids = explode(',', str_replace('"', '', $project_curr['groupes'] ?? ""));
    return in_array($g['id'], $groupes_ids);
  });

  $programmes_project = array_filter($programmes, function ($p) use ($project_curr) {
    $programmes_ids = explode(',', str_replace('"', '', $project_curr['programmes'] ?? ""));
    return in_array($p['id'], $programmes_ids);
  });
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
      <div class="row mx-n5 mt-n5 g-3 mb-3">
        <div class="col-md-3">
          <div class="card rounded-1 primary h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="fa-solid fa-bullseye fs-3 text-primary"></span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="text-muted mb-2">Exécution physique</h6>
                  <h4 class="mb-1"><?php echo $exe_physique; ?>%</h4>
                  <small class="text-success">
                    <i class="fas fa-arrow-up me-1"></i><?php echo $finishedTacheCount; ?>/<?php echo $totalTacheCount; ?> activités
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card rounded-1 success h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="fa-solid fa-coins fs-3 text-success"></span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="text-muted mb-2">Exécution financière</h6>
                  <h4 class="mb-1"><?php echo $exe_financiere; ?>%</h4>
                  <small class="text-info">
                    <?php echo number_format($montant_total_depense, 0, ',', ' '); ?> USD / <?php echo number_format($budget_reference, 0, ',', ' '); ?> USD
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card rounded-1 warning h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="fa-solid fa-calendar-check fs-3 text-warning"></span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="text-muted mb-2">Période</h6>
                  <h6 class="mb-1"><?php echo date('d/m/Y', strtotime($project_curr['start_date'])); ?></h6>
                  <small>au <?php echo date('d/m/Y', strtotime($project_curr['end_date'])); ?></small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card rounded-1 info h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <span class="fa-solid fa-handshake fs-3 text-info"></span>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="text-muted mb-2">Partenaires</h6>
                  <h4 class="mb-1"><?php echo count($partenaires_project); ?></h4>
                  <small><?php echo count($conventions_project); ?> conventions</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mx-n4 mx-lg-n6 px-2 px-lg-3 bg-body-emphasis border-top">
        <ul class="nav nav-underline fs-9" id="projetTab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $tab == 'view' ? 'active' : ''; ?>" id="view-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=view'; ?>">
              <i class="uil uil-estate me-1"></i>Général
            </a>
          </li>
          <li class="nav-item" role="task">
            <a class="nav-link <?php echo $tab == 'task' ? 'active' : ''; ?>" id="task-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=task'; ?>">
              <i class="uil uil-clipboard-notes me-1"></i>Activités <span class="badge bg-primary-subtle text-primary"><?php echo count($taches_actives); ?></span>
            </a>
          </li>
          <li class="nav-item" role="indicateur">
            <a class="nav-link <?php echo $tab == 'indicator' ? 'active' : ''; ?>" id="indicator-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=indicator'; ?>">
              <i class="uil uil-chart-line me-1"></i>Indicateurs <span class="badge bg-primary-subtle text-primary"><?php echo count($indicateurs_project); ?></span>
            </a>
          </li>
          <li class="nav-item" role="finance">
            <a class="nav-link <?php echo $tab == 'finance' ? 'active' : ''; ?>" id="finance-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=finance'; ?>">
              <i class="uil uil-usd-circle me-1"></i>Soutiens financiers
            </a>
          </li>
          <li class="nav-item" role="synthese">
            <a class="nav-link <?php echo $tab == 'synthese' ? 'active' : ''; ?>" id="synthese-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=synthese'; ?>">
              <i class="uil uil-file-bookmark-alt me-1"></i>Synthèse
            </a>
          </li>
        </ul>
      </div>

      <div class="mx-n4 mx-lg-n6 px-2 px-lg-3 bg-body-emphasis border">
        <div class="tab-content mt-2" id="projetTabContent">
          <div class="tab-pane fade <?php echo $tab == 'view' ? 'active show' : ''; ?>" id="tab-view" role="tabpanel" aria-labelledby="view-tab">
            <?php include './components/tabs/tab_proj_home.php'; ?>
          </div>

          <div class="tab-pane fade <?php echo $tab == 'task' ? 'active show' : ''; ?>" id="tab-task" role="tabpanel" aria-labelledby="task-tab">
            <?php include './components/tabs/tab_proj_task.php'; ?>
          </div>

          <div class="tab-pane fade <?php echo $tab == 'indicator' ? 'active show' : ''; ?>" id="tab-indicator" role="tabpanel" aria-labelledby="indicator-tab">
            <?php include './components/tabs/tab_proj_indic.php'; ?>
          </div>

          <div class="tab-pane fade <?php echo $tab == 'finance' ? 'active show' : ''; ?>" id="tab-finance" role="tabpanel" aria-labelledby="finance-tab">
            <?php include './components/tabs/tab_proj_finance.php'; ?>
          </div>

          <div class="tab-pane fade <?php echo $tab == 'synthese' ? 'active show' : ''; ?>" id="tab-synthese" role="tabpanel" aria-labelledby="synthese-tab">
            <?php include './components/tabs/tab_proj_synthese.php'; ?>
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
  <script src="assets/scripts/chart-pie.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>