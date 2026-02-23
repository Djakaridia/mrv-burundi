<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Groupe de travail | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $tab = isset($_GET['tab']) ? $_GET['tab'] : 'group';

  if (!in_array($tab, ['group', 'meet'])) {
    $tab = 'group';
  }

  $user = new User($db);
  $users = $user->read();

  $groupe = new GroupeTravail($db);
  $groupes = $groupe->read();

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) {
    return $structure['state'] == 'actif';
  });

  $reunion = new Reunion($db);
  $reunions = $reunion->read();

  $secteur = new Secteur($db);
  $secteurs = $secteur->read();
  $secteurs = array_filter($secteurs, function ($secteur) {
    return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
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
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'group' ? 'active' : ''; ?>" id="group-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=group'; ?>">Groupe de travail</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'meet' ? 'active' : ''; ?>" id="meet-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=meet'; ?>">Calendrier des réunions</a></li>
      </ul>

      <div class="tab-content mt-5" id="myTabContent">
        <div class="tab-pane fade <?php echo $tab == 'group' ? 'active show' : ''; ?>" id="tab-group" role="tabpanel" aria-labelledby="group-tab">
          <?php include './components/tabs/tab_groups.php'; ?>
        </div>
        <div class="tab-pane fade <?php echo $tab == 'meet' ? 'active show' : ''; ?>" id="tab-meet" role="tabpanel" aria-labelledby="meet-tab">
          <?php include './components/tabs/tab_reunion.php'; ?>
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
<script src="assets/scripts/event-calendar.js"></script>
<script>
  const listReunions = <?php echo json_encode($reunions); ?>;
  const eventsReunions = listReunions.map(reunion => ({
    id: reunion.id,
    title: reunion.name,
    start: reunion.horaire,
    description: reunion.description,
    className: "text-primary",
    location: reunion.lieu,
    allDay: false
  }));
</script>

</html>