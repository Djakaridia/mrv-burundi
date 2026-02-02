<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Détails du groupe | MRV - Burundi</title>

  <?php
  $group_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
  $tab = isset($_GET['tab']) ? $_GET['tab'] : 'view';

  if (!in_array($tab, ['view', 'user', 'reunion', 'project'])) {
    $tab = 'view';
  }

  if ($group_id <= 0) {
    header("Location: groups.php");
    exit();
  }

  include './components/navbar & footer/head.php';

  $group = new GroupeTravail($db);
  $groupes = $group->read();
  $groupes = array_filter($groupes, function ($group) { return $group['state'] == 'actif'; });

  $user = new User($db);
  $users = $user->read();
  $users = array_filter($users, function ($user) { return $user['state'] == 'actif'; });

  $type_structure = new StructureType($db);
  $type_structures = $type_structure->read();

  //=============================//
  //=============================//
  $group = new GroupeTravail($db);
  $group->id = $group_id;
  $group_curr = $group->readById();

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) { return $structure['state'] == 'actif'; });

  $reunion = new Reunion($db);
  $reunions = $reunion->read();
  $reunions = array_filter($reunions, function ($g) use ($group_id) { return $g['groupe_id'] == $group_id; });

  $projet = new Projet($db);
  $projets = $projet->read();
  $projets = array_filter($projets, function ($g) use ($group_id) {
    $groupes_ids = explode(',', str_replace('"', '', $g['groupes']));
    return in_array($group_id, $groupes_ids);
  });

  $group_user = new GroupeUsers($db);
  $group_user->groupe_id = $group_id;
  $users_group = $group_user->readByGroupeId();
  $users_no_in_group = array_filter($users, function ($user) use ($users_group) {
    foreach ($users_group as $g) {
      if ($g['user_id'] == $user['id']) {
        return false;
      }
    }
    return true;
  });

  $dossier = new Dossier($db);
  $dossiers = $dossier->read();

  $dossier->type = 'groups';
  $dossier_groupe = $dossier->readByType();

  $dossier->type = 'reunion';
  $dossier_reunion = $dossier->readByType();

  if (empty($group_curr)) {
    echo "<script> window.location.href = 'groups.php';</script>";
    exit();
  }

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
            id="view-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $group_id . '&tab=view'; ?>">Aperçu</a></li>
        <li class="nav-item" role="user"><a class="nav-link <?php echo $tab == 'user' ? 'active' : ''; ?>" id="user-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $group_id . '&tab=user'; ?>">Membres</a></li>
        <li class="nav-item" role="Reunion"><a class="nav-link <?php echo $tab == 'reunion' ? 'active' : ''; ?>"
            id="reunion-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $group_id . '&tab=reunion'; ?>">Réunions</a></li>
        <li class="nav-item" role="projet"><a class="nav-link <?php echo $tab == 'project' ? 'active' : ''; ?>"
            id="project-tab"
            href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $group_id . '&tab=project'; ?>">Projets</a></li>
      </ul>

      <div class="mx-n4 mx-lg-n6 px-0 bg-body-emphasis border">
        <div class="tab-content mt-2" id="myTabContent">
          <div class="tab-pane fade <?php echo $tab == 'view' ? 'active show' : ''; ?>" id="tab-view" role="tabpanel"
            aria-labelledby="view-tab">
            <?php include './components/tabs/tab_group_home.php'; ?>
          </div>
          <div class="tab-pane fade <?php echo $tab == 'user' ? 'active show' : ''; ?>" id="tab-user" role="tabpanel"
            aria-labelledby="user-tab">
            <?php include './components/tabs/tab_group_user.php'; ?>
          </div>
          <div class="tab-pane fade <?php echo $tab == 'reunion' ? 'active show' : ''; ?>" id="tab-reunion"
            role="tabpanel" aria-labelledby="reunion-tab">
            <?php include './components/tabs/tab_group_reunion.php'; ?>
          </div>
          <div class="tab-pane fade <?php echo $tab == 'project' ? 'active show' : ''; ?>" id="tab-project"
            role="tabpanel" aria-labelledby="project-tab">
            <?php include './components/tabs/tab_group_project.php'; ?>
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