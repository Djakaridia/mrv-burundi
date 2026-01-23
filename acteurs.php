<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Acteurs | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $tab = isset($_GET['tab']) ? $_GET['tab'] : 'actor';

  if (!in_array($tab, ['actor', 'type'])) {
    $tab = 'actor';
  }

  $structure = new Structure($db);
  $structures = $structure->read();

  $type_structure = new StructureType($db);
  $type_structures = $type_structure->read();

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
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'actor' ? 'active' : ''; ?>" id="actor-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=actor'; ?>">Acteurs</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'type' ? 'active' : ''; ?>" id="type-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=type'; ?>">Types Acteurs</a></li>
      </ul>

      <div class="tab-content mt-5" id="myTabContent">
        <div class="tab-pane fade <?php echo $tab == 'actor' ? 'active show' : ''; ?>" id="tab-home" role="tabpanel" aria-labelledby="home-tab">
          <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0 border-start-0">
            <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
              <div class="col-auto">
                <h4 class="my-1 fw-black">Liste des acteurs</h4>
              </div>

              <button title="Ajouter un acteur" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addStructureModal" aria-haspopup="true" aria-expanded="false"
                data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un acteur</button>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                  <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                    <thead class="bg-primary-subtle">
                      <tr>
                        <th class="sort align-middle" scope="col">#</th>
                        <th class="sort align-middle" scope="col" data-sort="product">Code</th>
                        <th class="sort align-middle" scope="col" data-sort="rating">Sigle</th>
                        <th class="sort align-middle" scope="col" data-sort="rating">Email</th>
                        <th class="sort align-middle" scope="col" data-sort="rating">Contact</th>
                        <th class="sort align-middle" scope="col" data-sort="review">Type</th>
                        <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="list" id="table-latest-review-body">
                      <?php foreach ($structures as $structure) {
                        $logoStruc = explode("../", $structure['logo'] ?? ''); ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                          <td class="align-middle product py-1">
                            <?php if ($structure['logo']) { ?>
                              <img class="d-block rounded-1 w-100 object-fit-contain" src="<?php echo end($logoStruc) ?>" alt="Logo" height="35" />
                            <?php } else { ?>
                              <div class="d-block rounded-1 border border-translucent text-center p-1 text-primary">
                                <i class="fas fa-users fs-8 p-1"></i>
                              </div>
                            <?php } ?>
                          </td>
                          <td class="align-middle product"><?php echo $structure['code']; ?></td>
                          <td class="align-middle rating"><?php echo $structure['sigle']; ?></td>
                          <td class="align-middle rating"><?php echo $structure['email']; ?></td>
                          <td class="align-middle rating"><?php echo $structure['phone']; ?></td>
                          <td class="align-middle review">
                            <?php foreach ($type_structures as $type_structure) { ?>
                              <?php if ($type_structure['id'] == $structure['type_id']) { ?>
                                <?php echo $type_structure['name']; ?>
                              <?php } ?>
                            <?php } ?>
                          </td>
                          <td class="align-middle">
                            <div class="position-relative d-flex gap-1">
                              <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                  data-bs-target="#addStructureModal" data-id="<?php echo $structure['id']; ?>">
                                  <span class="uil-pen fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="<?php echo $structure['state'] == 'actif' ? 'Désactiver' : 'Activer'; ?>" onclick="updateState(<?php echo $structure['id']; ?>, '<?php echo $structure['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $structure['state'] == 'actif' ? 'désactiver' : 'activer'; ?> cet acteur ?', 'structures')"
                                  type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                  <span class="uil-<?php echo $structure['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'delete', 2)) : ?>
                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                  onclick="deleteData(<?php echo $structure['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cet acteur ?', 'structures')">
                                  <span class="uil-trash-alt fs-8"></span>
                                </button>
                              <?php endif; ?>
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


        <!-- Sous-acteurs -->
        <div class="tab-pane fade <?php echo $tab == 'type' ? 'active show' : ''; ?>" id="tab-type" role="tabpanel" aria-labelledby="type-tab">
          <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
            <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
              <div class="col-auto">
                <h4 class="my-1 fw-black">Liste des types acteurs</h4>
              </div>

              <div class="ms-lg-2">
                <button title="Ajouter un type acteur" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                  data-bs-target="#addStructureTypeModal" aria-haspopup="true" aria-expanded="false"
                  data-bs-reference="child">
                  <i class="fas fa-plus"></i> Ajouter un type acteur</button>
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
                        <th class="sort align-middle" scope="col" data-sort="product">
                          Nom
                        </th>
                        <th class="sort align-middle" scope="col" data-sort="product">
                          Description
                        </th>

                        <th class="sort align-middle" scope="col" data-sort="time" style="min-width:100px;">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody class="list" id="table-latest-review-body">
                      <?php foreach ($type_structures as $type_structure): ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                          <td class="align-middle customer">
                            <?php echo $type_structure['name'] ?>
                          </td>
                          
                          <td class="align-middle customer">
                            <?php echo $type_structure['description'] ?>
                          </td>

                          <td class="align-middle review">
                            <div class="position-relative">
                              <div class="">
                                <?php if (checkPermis($db, 'update')) : ?>
                                  <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addStructureTypeModal"
                                    data-id="<?php echo $type_structure['id'] ?>"
                                    class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                    <span class="uil-pen fs-8"></span>
                                  </button>
                                <?php endif; ?>

                                <?php if (checkPermis($db, 'delete')) : ?>
                                  <button title="Supprimer" onclick="deleteData(<?php echo $type_structure['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce type acteur ?', 'structure_types')"
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