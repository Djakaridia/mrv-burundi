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

  $type_structure = new StructureType($db);
  $type_structures = $type_structure->read();

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

  $groupe_travail = new GroupeTravail($db);
  $groupes_travail = $groupe_travail->read();

  //=============================//
  //=============================//
  $project = new Projet($db);
  $project->id = $project_id;
  $project_curr = $project->readById();

  if (empty($project_curr)) {
    header("Location: projects.php");
    exit();
  }

  $projet_gaz = explode(',', $project_curr['gaz_type']);

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

  $referentiel = new Referentiel($db);
  $referentiels = $referentiel->read();
  $referentiels = array_filter($referentiels, function ($referentiel) {
    return $referentiel['state'] == 'actif';
  });

  $niveau_resultat = new NiveauResultat($db);
  $niveau_resultats = $niveau_resultat->read();

  $indicateur = new Indicateur($db);
  $indicateur->projet_id = $project_id;
  $indicateurs_project = $indicateur->readByProjet();

  $convention = new Convention($db);
  $convention->projet_id = $project_id;
  $conventions_project = $convention->readByProjet();
  $arr_conventIds = array_column($conventions_project, 'structure_id');

  $structures_project = [];
  foreach ($structures as $structure) {
    if (in_array($structure['id'], $arr_conventIds)) {
      array_push($structures_project, $structure);
    }
  }

  $secteurs_project = array_filter($secteurs, function ($s) use ($project_curr) {
    $secteurs_ids = explode(',', str_replace('"', '', $project_curr['secteurs'] ?? ""));
    return in_array($s['id'], $secteurs_ids);
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
      <ul class="nav nav-underline fs-9 mt-n4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'view' ? 'active' : ''; ?>"
            id="view-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=view'; ?>">Général</a>
        </li>
        <li class="nav-item" role="task"><a class="nav-link <?php echo $tab == 'task' ? 'active' : ''; ?>" id="task-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=task'; ?>">Activités</a></li>
        <li class="nav-item" role="indicateur"><a class="nav-link <?php echo $tab == 'indicator' ? 'active' : ''; ?>"
            id="indicator-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=indicator'; ?>">Indicateurs</a></li>
        <!-- <li class="nav-item" role="member"><a class="nav-link <php echo $tab == 'member' ? 'active' : ''; ?>" id="member-tab" data-bs-toggle="tab" href="#tab-member" role="tab" aria-controls="tab-member" aria-selected="true">Structures</a></li> -->
        <li class="nav-item" role="finance"><a class="nav-link <?php echo $tab == 'finance' ? 'active' : ''; ?>"
            id="finance-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=finance'; ?>">Financements</a></li>
        <li class="nav-item" role="synthese"><a class="nav-link <?php echo $tab == 'synthese' ? 'active' : ''; ?>"
            id="synthese-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=synthese'; ?>">Synthèse</a></li>
      </ul>

      <div class="mx-n4 mx-lg-n6 px-2 px-lg-3 bg-body-emphasis border">
        <div class="tab-content mt-2" id="myTabContent">
          <div class="tab-pane fade <?php echo $tab == 'view' ? 'active show' : ''; ?>" id="tab-view" role="tabpanel"
            aria-labelledby="view-tab">
            <?php include './components/tabs/tab_proj_home.php'; ?>
          </div>
          <div class="tab-pane fade <?php echo $tab == 'task' ? 'active show' : ''; ?>" id="tab-task" role="tabpanel"
            aria-labelledby="task-tab">
            <?php include './components/tabs/tab_proj_task.php'; ?>
          </div>
          <div class="tab-pane fade <?php echo $tab == 'indicator' ? 'active show' : ''; ?>" id="tab-indicator"
            role="tabpanel" aria-labelledby="indicator-tab">
            <?php include './components/tabs/tab_proj_indic.php'; ?>
          </div>
          <!-- <div class="tab-pane fade <php echo $tab == 'member' ? 'active show' : ''; ?>" id="tab-member" role="tabpanel" aria-labelledby="member-tab">
            <php include './components/tabs/tab_proj_struc.php'; ?>
          </div> -->
          <div class="tab-pane fade <?php echo $tab == 'finance' ? 'active show' : ''; ?>" id="tab-finance"
            role="tabpanel" aria-labelledby="finance-tab">
            <?php include './components/tabs/tab_proj_finance.php'; ?>
          </div>
          <div class="tab-pane fade <?php echo $tab == 'synthese' ? 'active show' : ''; ?>" id="tab-synthese"
            role="tabpanel" aria-labelledby="synthese-tab">
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
</body>
<script src="assets/scripts/chart-pie.js"></script>

</html>