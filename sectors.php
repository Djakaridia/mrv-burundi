<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Secteurs | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $secteur = new Secteur($db);
  $data_secteurs = $secteur->read();
  $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
    return $secteur['parent'] == 0;
  });
  $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] > 0;
  });
  $actions_prio = new ActionPrioritaire($db);
  $data_actions_prio = $actions_prio->read();
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
      <?php if (!isset($_GET['id']) || $_GET['id'] == 0 || $_GET['id'] == null || $_GET['id'] == '' || !is_numeric($_GET['id'])): ?>
        <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
          <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
            <div class="col-auto">
              <h4 class="my-1 fw-black">Liste des secteurs</h4>
            </div>

            <div class="ms-lg-2 d-flex gap-2">
              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addSecteurModal" aria-haspopup="true" aria-expanded="false"
                data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un secteur</button>

              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addSousSecteurBtn" data-bs-toggle="modal"
                data-bs-target="#addSecteurModal" data-parent="-1" aria-haspopup="true" aria-expanded="false"
                data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un sous-secteur</button>
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-12">
            <div class="mx-n4 mx-lg-n6 bg-body-emphasis border-y">
              <div class="table-responsive p-1 scrollbar" style="min-height: 432px;">
                <table class="table fs-9 table-bordered mb-0 border-top border-translucent">
                  <thead class="bg-primary-subtle">
                    <tr class="text-center">
                      <th class="sort align-middle" scope="col" rowspan="2"> Code</th>
                      <th class="sort align-middle" scope="col" rowspan="2"> Intitulé</th>
                      <th class="sort align-middle" scope="col" rowspan="2"> Actions Prioritaires</th>
                      <th class="sort align-middle" scope="col" colspan="2"> Structure responsable</th>
                      <th class="sort align-middle" scope="col" rowspan="2" style="min-width:100px;">Actions</th>
                    </tr>
                    <tr class="text-center">
                      <th class="sort align-middle" scope="col" rowspan="2"> Nature des données</th>
                      <th class="sort align-middle" scope="col" rowspan="2"> Sources des données</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($secteurs as $secteur):
                      $sous_secteur_items = array_filter($data_secteurs, function ($sous_secteur_item) use ($secteur) {
                        return $sous_secteur_item['parent'] == $secteur['id'];
                      });
                      sort($sous_secteur_items)
                    ?>
                      <tr class="bg-light">
                        <td class="align-middle px-2"><?= $secteur['code'] ?></td>
                        <td class="align-middle px-2"><?= $secteur['name'] ?></td>
                        <td class="align-middle px-2 text-center">
                          <span class="text-primary p-0 m-0">
                            Sous-secteurs <span class="badge bg-primary px-1 text-nowrap"><?= count($sous_secteur_items) ?></span>
                          </span>
                        </td>
                        <td class="align-middle px-2 text-center" colspan="2"><?= $secteur['organisme'] ?></td>
                        <td class="align-middle px-2">
                          <div class="position-relative">
                            <div class="d-flex gap-1">
                              <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                  data-bs-target="#addSecteurModal" data-id="<?php echo $secteur['id']; ?>">
                                  <span class="uil-pen fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="Activer/Désactiver" onclick="updateState(<?php echo $secteur['id']; ?>, '<?php echo $secteur['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $secteur['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce secteur ?', 'secteurs')"
                                  type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                  <span class="uil-<?php echo $secteur['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'delete')) : ?>
                                <button title="Supprimer" onclick="deleteData(<?php echo $secteur['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce secteur ?', 'secteurs')"
                                  type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                  <span class="uil-trash-alt fs-8"></span>
                                </button>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                      </tr>

                      <?php foreach ($sous_secteur_items as $sous_secteur_item):
                        $sous_secteur_actions = array_filter($data_actions_prio, function ($action_prio) use ($sous_secteur_item) {
                          return $action_prio['secteur_id'] == $sous_secteur_item['id'];
                        });
                      ?>
                        <tr>
                          <td class="align-middle px-2"><?= $sous_secteur_item['code'] ?></td>
                          <td class="align-middle px-2"><?= $sous_secteur_item['name'] ?></td>
                          <td class="align-middle px-2">
                            <button title="Voir" type="button" class="btn btn-sm btn-link text-warning text-nowrap p-0 m-0" onclick="window.location.href='sectors.php?id=<?= $sous_secteur_item['id'] ?>'">
                              Actions prioritaires <span class="badge bg-warning px-1"><?= count($sous_secteur_actions) ?></span>
                            </button>
                          </td>
                          <td colspan="2" class="p-0">
                            <table class="table fs-9 m-0">
                              <?php
                              $natures = !empty($sous_secteur_item['nature']) && $sous_secteur_item['nature'] !== 'N/A' ? explode(' | ', $sous_secteur_item['nature']) : [];
                              $sources = !empty($sous_secteur_item['source']) && $sous_secteur_item['source'] !== 'N/A' ? explode(' | ', $sous_secteur_item['source']) : [];
                              $maxRows = max(count($natures), count($sources));

                              for ($i = 0; $i < $maxRows; $i++) {
                                $nature = isset($natures[$i]) ? htmlspecialchars(trim($natures[$i])) : '';
                                $source = isset($sources[$i]) ? htmlspecialchars(trim($sources[$i])) : '';
                              ?>
                                <tr>
                                  <td class="border-end align-middle px-2" style="width: 50%;"><?= $nature ?></td>
                                  <td class="align-middle px-2" style="width: 50%;"><?= $source ?></td>
                                </tr>
                              <?php } ?>
                            </table>
                          </td>
                          <td class="align-middle px-2">
                            <div class="d-flex gap-1">
                              <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                  data-bs-target="#addSecteurModal" data-parent="-1" data-id="<?php echo $sous_secteur_item['id']; ?>">
                                  <span class="uil-pen fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="Activer/Désactiver" onclick="updateState(<?php echo $sous_secteur_item['id']; ?>, '<?php echo $sous_secteur_item['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $sous_secteur_item['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce sous-secteur ?', 'secteurs')"
                                  type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                  <span class="uil-<?php echo $sous_secteur_item['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'delete')) : ?>
                                <button title="Supprimer" onclick="deleteData(<?php echo $sous_secteur_item['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce sous-secteur ?', 'secteurs')"
                                  type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                  <span class="uil-trash-alt fs-8"></span>
                                </button>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>

                    <?php endforeach; ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Sous-secteurs -->
      <?php if (isset($_GET['id']) && $_GET['id'] > 0 && is_numeric($_GET['id']) && $_GET['id'] != null && $_GET['id'] != ''):
        $id_parent = $_GET['id'];
        $secteur_parent = array_filter($sous_secteurs, function ($secteur) use ($id_parent) {
          return $secteur['id'] == $id_parent;
        });

        $actions_prio_child = array_filter($data_actions_prio, function ($action) use ($id_parent) {
          return $action['secteur_id'] == $id_parent;
        });
      ?>
        <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
          <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
            <div class="col-auto">
              <h4 class="my-1 fw-black">Liste des actions prioritaires du secteur
                <span class="badge bg-primary px-1"><?php echo array_pop($secteur_parent)['name'] ?></span>
              </h4>
            </div>
            <div class="ms-lg-2 d-flex gap-2">
              <button title="Retour" onclick="window.location.href='sectors.php'" class="btn btn-subtle-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
              </button>

              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addActionPrioModal" data-parent="<?php echo $id_parent ?>"
                aria-haspopup="true" aria-expanded="false" data-bs-reference="child">
                <i class="fas fa-plus"></i> Ajouter une action</button>
            </div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-12">
            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
              <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                  <thead class="bg-primary-subtle">
                    <tr>
                      <th class="sort align-middle" scope="col"> Code</th>
                      <th class="sort align-middle" scope="col"> Intitulé</th>
                      <th class="sort align-middle" scope="col"> Secteur</th>
                      <th class="sort align-middle" scope="col" style="min-width:100px;"> Actions</th>
                    </tr>
                  </thead>
                  <tbody class="list" id="table-latest-review-body">
                    <?php foreach ($actions_prio_child as $action_prio): ?>
                      <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                        <td class="align-middle customer"> <?php echo $action_prio['code'] ?> </td>
                        <td class="align-middle customer"> <?php echo $action_prio['name'] ?> </td>
                        <td class="align-middle customer">
                          <?php foreach ($sous_secteurs as $secteur): ?>
                            <?php if ($action_prio['secteur_id'] == $secteur['id']): ?>
                              <?php echo $secteur['name']; ?>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </td>
                        <td class="align-middle review">
                          <div class="position-relative">
                            <div class="">
                              <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addActionPrioModal"
                                  data-parent="<?php echo $action_prio['secteur_id'] ?>" data-id="<?php echo $action_prio['id'] ?>"
                                  class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                  <span class="uil-pen fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'delete')) : ?>
                                <button title="Supprimer" onclick="deleteData(<?php echo $action_prio['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette action ?', 'actions')"
                                  type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                  <span class="uil-trash-alt fs-8"></span>
                                </button>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>