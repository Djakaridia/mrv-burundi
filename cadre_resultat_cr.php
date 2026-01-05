<!DOCTYPE html>
<html
  lang="fr"
  dir="ltr"
  data-navigation-type="default"
  data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Cadre de résultats | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $sel_id = isset($_GET['proj']) ? $_GET['proj'] : '';

  $projet = new Projet($db);
  $projets = $projet->read();
  $projets = array_filter($projets, function ($projet) {
    return $projet['state'] == 'actif';
  });

  if (!empty($projets) && $sel_id == '') {
    $sel_id = $projets[0]['id'];
  }

  $niveau_resultat = new NiveauResultat($db);
  $niveau_resultats = $niveau_resultat->read();

  $indicateur = new Indicateur($db);
  if ($sel_id && $sel_id != '') {
    $indicateur->projet_id = $sel_id;
    $indicateurs = $indicateur->readByProjet();
  } else {
    $indicateurs = $indicateur->read();
  }

  $structure = new Structure($db);
  $structures = $structure->read();

  $referentiel = new Referentiel($db);
  $referentiels = $referentiel->read();
  $referentiels = array_filter($referentiels, function ($referentiel) {
    return $referentiel['state'] == 'actif';
  });

  $unite = new Unite($db);
  $unites = $unite->read();
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
        <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center">
          <div class="col-lg-4 mb-2 mb-lg-0">
            <h4 class="my-1 fw-black">Indicateurs du CMR</h4>
          </div>

          <div class="col-lg-3 mb-2 mb-lg-0 text-center">
            <form action="formNiveauResultat" method="post">
              <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="result" id="resultID" onchange="window.location.href = 'cadre_resultat_cr.php?proj=' + this.value">
                <option class="text-center" value="" selected disabled>---Sélectionner un projet---</option>
                <?php foreach ($projets as $projet) { ?>
                  <option value="<?php echo $projet['id']; ?>" <?php if ($sel_id == $projet['id']) echo 'selected'; ?>><?php echo $projet['name']; ?></option>
                <?php } ?>
              </select>
            </form>
          </div>

          <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addIndicateurModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
              <i class="fas fa-plus"></i> Ajouter un indicateur</button>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
            <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
              <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="bg-secondary-subtle">
                  <tr>
                    <th class="sort align-middle" scope="col"> Code </th>
                    <th class="sort align-middle" scope="col"> Intitule </th>
                    <th class="sort align-middle" scope="col"> Unité </th>
                    <th class="sort align-middle" scope="col"> Calcul </th>
                    <th class="sort align-middle" scope="col"> Responsable </th>
                    <th class="sort align-middle" scope="col"> Valeur référence </th>
                    <th class="sort align-middle" scope="col"> Valeur cible </th>
                    <th class="sort align-middle" scope="col"> Status </th>
                    <th class="sort align-middle" scope="col" style="min-width:100px;"> Actions </th>
                  </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                  <?php foreach ($indicateurs as $indicateur) { ?>
                    <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                      <td class="align-middle py-0"><?php echo $indicateur['code']; ?></td>
                      <td class="align-middle"><?php echo $indicateur['intitule']; ?></td>

                      <td class="align-middle py-0">
                        <?php foreach ($unites as $unite) { ?>
                          <?php if ($unite['id'] == $indicateur['unite']) { ?>
                            <?php echo $unite['name']; ?>
                          <?php } ?>
                        <?php } ?>
                      </td>
                      <td class="align-middle"><?php echo listModeCalcul()[$indicateur['mode_calcul']]; ?></td>
                      <td class="align-middle">
                        <?php foreach ($structures as $structure) { ?>
                          <?php if ($structure['id'] == $indicateur['responsable']) { ?>
                            <?php echo $structure['sigle']; ?>
                          <?php } ?>
                        <?php } ?>
                      </td>

                      <td class="align-middle py-0"><?php echo $indicateur['valeur_reference']; ?></td>
                      <td class="align-middle py-0"><?php echo $indicateur['valeur_cible']; ?></td>

                      <td class="align-middle customer">
                        <span class="badge rounded-pill badge-phoenix fs-10 badge-phoenix-<?php echo $indicateur['state'] == 'actif' ? 'success' : 'danger'; ?>">
                          <span class="badge-label"><?php echo $indicateur['state'] == 'actif' ? 'Actif' : 'Inactif'; ?></span>
                        </span>
                      </td>

                      <td class="align-middle review">
                        <div class="position-relative">
                          <div class="d-flex gap-1">
                            <?php if (checkPermis($db, 'update')) : ?>
                              <button title="Modifier" data-bs-toggle="modal" data-bs-target="#addIndicateurModal" data-id="<?php echo $indicateur['id']; ?>" aria-haspopup="true" aria-expanded="false"
                                class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1">
                                <span class="uil-pen fs-8"></span>
                              </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'update', 2)) : ?>
                              <button title="Activer/desactiver" onclick="updateState(<?php echo $indicateur['id']; ?>, '<?php echo $indicateur['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $indicateur['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce indicateur ?', 'indicateurs')"
                                type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                <span class="uil-<?php echo $indicateur['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                              </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'delete')) : ?>
                              <button title="Supprimer" onclick="deleteData(<?= $indicateur['id'] ?>, 'Voulez-vous vraiment supprimer cet indicateur ?', 'indicateurs')"
                                class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                <span class="uil-trash-alt fs-8"></span>
                              </button>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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